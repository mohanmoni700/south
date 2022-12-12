<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Helper;

class Curl extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $http;

    /**
     * [__construct description]
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
    ) {
        parent::__construct($context);
        $this->http = $curlFactory->create();
    }

    /**
     * get a response
     * @param  boolean $isJsonDecoded
     * @return string
     */
    public function getResponse($isJsonDecoded = true)
    {
        $response = $this->http->read();
        $responseCode = \Zend_Http_Response::extractCode($response);
        $responseBody = \Zend_Http_Response::extractBody($response);

        $curlErrorNo = $this->http->getErrno();

        if ($curlErrorNo) {
            $curlResponse['response'] = $this->getCurlErrorIdentifier($curlErrorNo);
            $curlResponse['isValid'] = false;
        } elseif ($responseCode == 200 || $responseCode == 400) {
            if ($isJsonDecoded) {
                $curlResponse['response'] = json_decode($responseBody, true);
                $curlResponse['isValid'] = true;
            } else {
                $curlResponse['response'] = $responseBody;
                $curlResponse['isValid'] = true;
            }
        } else {
            if (!empty($responseBody)) {
                $curlResponse['response'] = $responseBody;
            } else {
                $curlResponse['response'] = 'ERROR_GENERAL_NORESPONSE';
            }
            $curlResponse['isValid'] = false;
        }

        $this->http->close();
        return $curlResponse;
    }

    /**
     * Get a response data from the gateway
     * @param  string $data
     * @param  string $url
     * @param  array $proxyParameters
     * @param  string $serverMode
     * @param  string $bearerToken
     * @return array|boolean
     */
    public function getResponseData($data, $url, $proxyParameters, $serverMode, $bearerToken = '')
    {
        $this->setSSLVerifypeer($serverMode);
        if (isset($proxyParameters['behind']) && $proxyParameters['behind']) {
            $this->http->addOption(CURLOPT_PROXY, $proxyParameters['url'].':'.$proxyParameters['port']);
        }
        $headers = [];
        if(!empty($bearerToken))
        {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }
        $this->http->write(\Zend_Http_Client::POST, $url, $http_ver = '1.1', $headers, $data);

        return $this->getResponse();
    }

    /**
     * Get a payment response from the gateway
     * @param  string $url
     * @param  array $proxyParameters
     * @param  string $serverMode
     * @param  string $bearerToken
     * @return array|boolean
     */
    public function getPaymentResponse($url, $proxyParameters, $serverMode, $bearerToken = '')
    {
        $this->setSSLVerifypeer($serverMode);
        if (isset($proxyParameters['behind']) && $proxyParameters['behind']) {
            $this->http->addOption(CURLOPT_PROXY, $proxyParameters['url'].':'.$proxyParameters['port']);
        }
        $headers = [];
        if(!empty($bearerToken))
        {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        $this->http->write(\Zend_Http_Client::GET, $url, $http_ver = '1.1', $headers);

        return $this->getResponse();
    }

    /**
     * Send the deregistration payment account to the gateway
     * @param  string $url
     * @param  array $proxyParameters
     * @param  string $serverMode
     * @param  string $bearerToken
     * @return array|boolean
     */
    public function sendDeRegistration($url, $proxyParameters, $serverMode, $bearerToken = '')
    {
        $this->setSSLVerifypeer($serverMode);
        if (isset($proxyParameters['behind']) && $proxyParameters['behind']) {
            $this->http->addOption(CURLOPT_PROXY, $proxyParameters['url'].':'.$proxyParameters['port']);
        }
        $headers = [];
        if(!empty($bearerToken))
        {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        $this->http->addOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->http->write(\Zend_Http_Client::DELETE, $url, $http_ver = '1.1', $headers);

        return $this->getResponse();
    }

    /**
     * get a xml response
     * @param  string $url
     * @param  string $xmlRequest
     * @param  array $proxyParameters
     * @param  string $serverMode
     * @param  string $bearerToken
     * @return void
     */
    public function getXmlResponse($url, $xmlRequest, $proxyParameters, $serverMode, $bearerToken = '')
    {
        $this->setSSLVerifypeer($serverMode);
        if (isset($proxyParameters['behind']) && $proxyParameters['behind']) {
            $this->http->addOption(CURLOPT_PROXY, $proxyParameters['url'].':'.$proxyParameters['port']);
        }
        $headers = ['Content-type: application/x-www-form-urlencoded;charset=UTF-8'];
        if(!empty($bearerToken))
        {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }
        $this->http->write(\Zend_Http_Client::POST, $url, $http_ver = '1.1', $headers, $xmlRequest);

        return $this->getResponse($isJsonDecoded = false);
    }

    /**
     * get payment widget content
     * @param string $paymentWidgetUrl
     * @param array $proxy
     * @param string $serverMode
     * @param string $bearerToken
     * @return void
     */
    public function getPaymentWidgetContent($paymentWidgetUrl, $proxy, $serverMode, $bearerToken = '')
    {
        $this->setSSLVerifypeer($serverMode);
        if (isset($proxy['behind']) && $proxy['behind']) {
            $this->http->addOption(CURLOPT_PROXY, $proxy['url'].':'.$proxy['port']);
        }
        $headers = [];
        if(!empty($bearerToken))
        {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }
        $this->http->write(\Zend_Http_Client::GET, $paymentWidgetUrl, '1.1', $headers);

        return $this->getResponse($isJsonDecoded = false);
    }

    /**
     * Sets the ssl verifypeer.
     * @param string $serverMode
     */
    public function setSSLVerifypeer($serverMode = 'LIVE')
    {
        if ($serverMode == 'TEST') {
            $this->http->setConfig(['verifypeer' => false]);
        }
    }

    /**
     * get curl error identifier
     *
     * @param string $code
     * @return string
     */
    public function getCurlErrorIdentifier($code)
    {
        $errorMessages = [
            '60' => 'ERROR_MERCHANT_SSL_CERTIFICATE'
        ];

        if (isset($errorMessages[$code])) {
            return $errorMessages[$code];
        } else {
            return 'ERROR_GENERAL_REDIRECT';
        }
    }
}
