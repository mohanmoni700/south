<?php

namespace Alfakher\Seamlesschex\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Zend\Http\Client;

class Data extends AbstractHelper
{
    public const CONFIG_PATH_ENABLE = "payment/seamlesschex/active";
    public const CONFIG_PATH_SANDBOX = "payment/seamlesschex/is_sandbox";
    public const CONFIG_PATH_TEST_ENDPOINT = "payment/seamlesschex/test_endpoint";
    public const CONFIG_PATH_TEST_PUBLISHABLE_KEY = "payment/seamlesschex/test_publishable_key";
    public const CONFIG_PATH_TEST_SECRET_KEY = "payment/seamlesschex/test_secret_key";
    public const CONFIG_PATH_LIVE_ENDPOINT = "payment/seamlesschex/live_endpoint";
    public const CONFIG_PATH_LIVE_PUBLISHABLE_KEY = "payment/seamlesschex/live_publishable_key";
    public const CONFIG_PATH_LIVE_SECRET_KEY = "payment/seamlesschex/live_secret_key";
    public const TYPE_CREATE = "create";
    public const TYPE_UPDATE = "update";
    public const TYPE_VOID = "void";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\HTTP\Adapter\Curl $advanceCurl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Alfakher\Seamlesschex\Model\SeamlesschexLogFactory $logFactory
     * @param Client $zendClient
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\HTTP\Adapter\CurlFactory $advanceCurl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Alfakher\Seamlesschex\Model\SeamlesschexLogFactory $logFactory,
        Client $zendClient
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curl = $curl;
        $this->_advanceCurl = $advanceCurl;
        $this->_encryptor = $encryptor;
        $this->_logFactory = $logFactory;
        $this->zendClient = $zendClient;

        parent::__construct($context);
    }

    /**
     * Get config data
     *
     * @param int $websiteId
     */
    public function getConfigData(
        $websiteId
    ) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $isActive = $this->_scopeConfig->getValue(self::CONFIG_PATH_ENABLE, $storeScope, $websiteId);
        if ($isActive) {
            $isSandbox = $this->_scopeConfig->getValue(self::CONFIG_PATH_SANDBOX, $storeScope, $websiteId);

            if ($isSandbox) {
                $apiEndpoint = $this->_scopeConfig->getValue(self::CONFIG_PATH_TEST_ENDPOINT, $storeScope, $websiteId);
                $secretKey = $this->_scopeConfig->getValue(self::CONFIG_PATH_TEST_SECRET_KEY, $storeScope, $websiteId);
            } else {
                $apiEndpoint = $this->_scopeConfig->getValue(self::CONFIG_PATH_LIVE_ENDPOINT, $storeScope, $websiteId);
                $secretKey = $this->_scopeConfig->getValue(self::CONFIG_PATH_LIVE_SECRET_KEY, $storeScope, $websiteId);
            }

            return [
                'endpoint' => $apiEndpoint,
                'secret_key' => $this->_encryptor->decrypt($secretKey),
            ];
        } else {
            return [];
        }
    }

    /**
     * Test connection
     *
     * @param int $websiteId
     */
    public function testConnection(
        $websiteId
    ) {
        $config = $this->getConfigData($websiteId);
        if (count($config)) {
            $this->_curl->addHeader("Content-Type", "application/json");
            $this->_curl->addHeader("Authorization", "Bearer " . $config['secret_key']);
            $this->_curl->get($config['endpoint'] . "check/list?limit=10&page=1&sort=date&direction=DESC");

            $responseStatus = $this->_curl->getStatus();
            $response = $this->_curl->getBody();

            if ($responseStatus == 200) {
                return ['status' => 1, 'message' => "Connection establised successfully"];
            } else {
                $errorResponse = json_decode($response, 1);
                $message['status'] = $responseStatus;
                $message['message'] = isset($errorResponse['message']) ? $errorResponse['message'] : "";
                $message['response'] = $response;
                return ['status' => 0, 'message' => json_encode($message)];
            }
        } else {
            return ['status' => 0, 'message' => "Please enable the Seamlesschex and configure"];
        }
    }

    /**
     * Update check
     *
     * @param object $order
     */
    public function updateCheck(
        $order
    ) {
        $config = $this->getConfigData($order->getStore()->getWebsiteId());
        if (count($config) && $order->getPayment()->getMethod() == "seamlesschex") {
            $paymentAdditionalInformation = $order->getPayment()->getAdditionalInformation();
            $data = [
                "check_id" => $paymentAdditionalInformation['check']['check_id'],
                "number" => $paymentAdditionalInformation['check']['number'],
                "amount" => $order->getTotalDue(),
                "memo" => "order #" . $order->getIncrementId() . " - updated",
                "name" => $order->getCustomerName(),
                "bank_account" => $order->getPayment()->getAchAccountNumber(),
                "bank_routing" => $order->getPayment()->getAchRoutingNumber(),
                "verify_before_save" => true,
            ];

            $jsonPayload = json_encode($data);

            $this->_curl->addHeader("Content-Type", "application/json");
            $this->_curl->addHeader("Authorization", "Bearer " . $config['secret_key']);
            $this->_curl->post($config['endpoint'] . "check/edit", $jsonPayload);

            $responseStatus = $this->_curl->getStatus();
            $response = $this->_curl->getBody();

            /* add logs; Start */
            $this->addLog(self::TYPE_UPDATE, $order->getIncrementId(), $jsonPayload, $response, $responseStatus);
            /* add logs; End */
        }
    }

    /**
     * Add log
     *
     * @param string $type
     * @param string $orderNumber
     * @param string $request
     * @param string $response
     * @param string $responseCode
     */
    public function addLog(
        $type,
        $orderNumber,
        $request,
        $response,
        $responseCode
    ) {
        try {
            $model = $this->_logFactory->create();
            $model->setType($type)
                ->setOrder($orderNumber)
                ->setRequest($request)
                ->setResponse($response)
                ->setResponseCode($responseCode)
                ->save();
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Void check
     *
     * @param object $order
     */
    public function voidCheck(
        $order
    ) {
        $config = $this->getConfigData($order->getStore()->getWebsiteId());
        if (count($config) && $order->getPayment()->getMethod() == "seamlesschex") {
            $paymentAdditionalInformation = $order->getPayment()->getAdditionalInformation();
            $checkId = $paymentAdditionalInformation['check']['check_id'];

            $this->zendClient->reset();
            $this->zendClient->setUri($config['endpoint'] . "check/" .
                $paymentAdditionalInformation['check']['check_id']);
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_DELETE);
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                "Authorization" => "Bearer " . $config['secret_key'],
            ]);
            $this->zendClient->setMethod('delete');
            $this->zendClient->setEncType('application/json');
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $decodedResponse = json_decode($response->getBody(), 1);

            /* add logs; Start */
            $this->addLog(
                self::TYPE_VOID,
                $order->getIncrementId(),
                $paymentAdditionalInformation['check']['check_id'],
                $response->getBody(),
                ''
            );
            /* add logs; End */

            if (isset($decodedResponse['error'])) {
                throw new \Magento\Framework\Validator\Exception(
                    __($decodedResponse['message'])
                );
            }
        }
    }
}
