<?php

namespace Alfakher\Seamlesschex\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const CONFIG_PATH_ENABLE = "payment/seamlesschex/active";
    const CONFIG_PATH_SANDBOX = "payment/seamlesschex/is_sandbox";
    const CONFIG_PATH_TEST_ENDPOINT = "payment/seamlesschex/test_endpoint";
    const CONFIG_PATH_TEST_PUBLISHABLE_KEY = "payment/seamlesschex/test_publishable_key";
    const CONFIG_PATH_TEST_SECRET_KEY = "payment/seamlesschex/test_secret_key";
    const CONFIG_PATH_LIVE_ENDPOINT = "payment/seamlesschex/live_endpoint";
    const CONFIG_PATH_LIVE_PUBLISHABLE_KEY = "payment/seamlesschex/live_publishable_key";
    const CONFIG_PATH_LIVE_SECRET_KEY = "payment/seamlesschex/live_secret_key";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curl = $curl;
        $this->_encryptor = $encryptor;

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
                'secret_key' => $this->_encryptor->decrypt($secretKey)
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
            $this->_curl->addHeader("Authorization", "Bearer ".$config['secret_key']);
            $this->_curl->get($config['endpoint']."check/list?limit=10&page=1&sort=date&direction=DESC");

            $responseStatus = $this->_curl->getStatus();
            $response = $this->_curl->getBody();

            if ($responseStatus == 200) {
                return ['status' => 1,'message' => "Connection establised successfully"];
            } else {
                $errorResponse = json_decode($response, 1);
                $message['status'] = $responseStatus;
                $message['message'] = isset($errorResponse['message']) ? $errorResponse['message'] : "";
                $message['response'] = $response;
                return ['status' => 0,'message' => json_encode($message)];
            }
        } else {
            return ['status' => 0,'message' => "Please enable the Seamlesschex and configure"];
        }
    }
}
