<?php

namespace Alfakher\PaymentEdit\Model;

use Magento\Payment\Gateway\Command\CommandException;

class Gateway extends \ParadoxLabs\FirstData\Model\Gateway
{
    /**
     * Run an auth transaction for $amount with the given payment info
     *
     * @param object $order
     * @param float $amount
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws CommandException
     */

    public function authorizeBackend($order, $amount)
    {
        $payment = $order->getPayment();
        $this->setParameter('transaction_type', 'authorize');
        $this->setParameter('amount', $amount);
        // Split this logic, if mode is 'Save only' we force an auth with a quote.
        $merchantRef = $order->getIncrementId();
        $currency = $order->getBaseCurrencyCode();
        
        $this->setParameter('merchant_ref', $merchantRef);
        $this->setParameter('currency', $currency);

        if ($payment->hasData('cc_cid') && $payment->getData('cc_cid') != '') {
            $this->setParameter('cvv', $payment->getData('cc_cid'));
        }

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    /**
     * Send the given request to First Data and process the results.
     *
     * @param array $params
     * @param string $path
     * @param bool $isApiTest
     * @return array
     * @throws CommandException
     */
    public function runTransaction($params, $path, $isApiTest = false)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/request.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('params');
        $logger->info(print_r($params, true));
        
        $auth = [
            'apikey'    => $this->getParameter('apikey'),
            'token'     => $this->getParameter('token'),
        ];

        $logger->info('auth');
        $logger->info(print_r($auth, true));
        /**
         * Check to see if transId is set to add it to endpoint
         * transId is needed for capture/invoice only, refund, and void
         */
        if ($this->hasParameter('transId')) {
            $path .= '/' . substr($this->getParameter('transId'), 0, strcspn($this->getParameter('transId'), '-'));
        }

        $logger->info('path');
        $logger->info(print_r($path, true));

        $httpClient = $this->getHttpClient($path);

        $paramData = json_encode($params);

        $header = $this->createHeader($auth, $paramData);
        $logger->info('header');
        $logger->info(print_r($header, true));
        $httpClient->setHeaders($header);

        $this->lastRequest = $paramData;

        try {
            $httpClient->setRawData($paramData, 'application/json');
            $response = $httpClient->request(\Zend_Http_Client::POST);
            $responseBody = $response->getBody();

            $this->lastResponse = $responseBody;

            $logger->info('responseBody');
            $logger->info(print_r($responseBody, true));
        
            $this->handleResponse($httpClient, json_decode($paramData, true), $isApiTest);
        } catch (\Zend_Http_Exception $e) {
            $this->helper->log(
                $this->code,
                sprintf(
                    "CURL Connection error: %s. %s (%s)\nREQUEST: %s",
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno(),
                    $this->sanitizeLog(json_decode($paramData, true))
                )
            );

            throw new CommandException(
                __(sprintf(
                    'First Data Gateway Connection error: %s. %s (%s)',
                    $e->getMessage(),
                    $httpClient->getAdapter()->getError(),
                    $httpClient->getAdapter()->getErrno()
                ))
            );
        }

        return $this->lastResponse;
    }

    // public function interpretTransaction($transactionResult)
    // {
    //     /**
    //      * Turn response into a consistent data object, as best we can
    //      */
    //     $data = $this->getDataFromResponse($transactionResult);

    //     /** @var \ParadoxLabs\TokenBase\Model\Gateway\Response $response */
    //     $response = $this->responseFactory->create();
    //     $response->setData($data);

    //     // Unclear on 17059, but the other fraud codes are detailed here: https://goo.gl/9HkMvb
    //     if (in_array($response->getResponseCode(), [17059, 200, 503, 596], false)) {
    //         $response->setIsFraud(true);
    //     }

    //     return $response;
    // }

    /**
     * Run a void transaction for the given payment info
     *
     * @param object $order
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws CommandException
     */
    public function voidBackend($order)
    {
        $this->setParameter('transaction_type', 'void');
        $payment     = $order->getPayment();
        
        if ($payment->getLastTransId() != '') {
            $this->setParameter('transId', $payment->getLastTransId());
        }

        if ($payment->getOrigData('base_amount_authorized') !== $payment->getAdditionalInformation('amount')) {
            $newAuthInfo = $payment->getAuthorizationTransaction()->getAdditionalInformation('raw_details_info');
            $this->setParameter('amount', $newAuthInfo['amount']);

            if ($newAuthInfo['reference_transaction_id'] != '') {
                $this->setParameter('transaction_tag', $newAuthInfo['reference_transaction_id']);
            }
        } else {
            if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
                $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
            }
            $this->setParameter('amount', $payment->getAdditionalInformation('amount'));
        }
        $this->setParameter('merchant_ref', $order->getIncrementId());
        $this->setParameter('currency', $order->getBaseCurrencyCode());
        
        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    // public function createTransactionAddPaymentInfo($params)
    // {
    //     $params['token'] = [
    //         'token_type' => 'FDToken',
    //         'token_data' => [
    //             'type'     => $this->getParameter('credit_card_type'),
    //             'value' => $this->getParameter('token_data_value'),
    //             'cardholder_name' => $this->getParameter('cardholder_name'),
    //             'exp_date' => $this->getParameter('exp_date'),
    //         ],
    //     ];

    //     /**
    //      * Send the CVV if it is set
    //      */
    //     if ($this->hasParameter('cvv')) {
    //         $params['token']['token_data']['cvv'] = $this->getParameter('cvv');
    //     }

    //     return $params;
    // }

    /**
     * Set parameter for backend.
     *
     * @param number $ccNumber
     * @param number $ccExpMonth
     * @param number $ccExpYear
     * @param number $ccCid
     * @param string $ccType
     * @param string $cardHolderName
     * @return $this
     * @throws CommandException
     */
    public function setParameterForBackend($ccNumber, $ccExpMonth, $ccExpYear, $ccCid, $ccType, $cardHolderName)
    {
        $this->setParameter('credit_card_type', $ccType);
        $this->setParameter('cardholder_name', $cardHolderName);
        $this->setParameter('card_number', $ccNumber);
        $this->setParameter('exp_date', sprintf('%02d%02d', $ccExpMonth, substr($ccExpYear, -2)));
        $this->setParameter('cvv', $ccCid);
    }

    // protected function getHttpClient($path)
    // {
    //     /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
    //     $httpClient = $this->httpClientFactory->create();

    //     $clientConfig = [
    //         'adapter'     => \Zend_Http_Client_Adapter_Curl::class,
    //         'timeout'     => 15,
    //         'curloptions' => [
    //             CURLOPT_CAINFO         => $this->moduleDir->getDir('ParadoxLabs_FirstData') . '/firstdata-cert.pem',
    //             CURLOPT_SSL_VERIFYPEER => false,
    //         ],
    //         'verifypeer' => false,
    //         'verifyhost' => 0,
    //     ];

    //     if ($this->verifySsl === true) {
    //         $clientConfig['curloptions'][CURLOPT_SSL_VERIFYPEER] = true;
    //         $clientConfig['curloptions'][CURLOPT_SSL_VERIFYHOST] = 2;
    //         $clientConfig['verifypeer'] = true;
    //         $clientConfig['verifyhost'] = 2;
    //     }

    //     $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
    //     $logger = new \Zend_Log();
    //     $logger->addWriter($writer);
    //     $logger->info('Payemnt');
    //     $logger->info($this->endpointTest);
    //     $logger->info($path);

    //     $httpClient->setUri($this->endpointTest . $path);
    //     $httpClient->setConfig($clientConfig);

    //     return $httpClient;
    // }
}
