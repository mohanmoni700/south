<?php

namespace HookahShisha\YotpoLoyalty\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Webapi\Rest\Request as WebapiRequest;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 *  YotpoData For taking the Config and Post Request
 */
class YotpoData
{
    protected const XML_PATH_SWELL_API_KEY = "yotpo_loyalty/general_settings/swell_api_key";
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var WebapiRequest
     */
    protected $_request;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * YotpoData Constructor
     *
     * @param Json $json
     * @param ScopeConfigInterface $scopeConfig
     * @param WebapiRequest $request
     * @param EncryptorInterface $encryptor
     * @param Logger $logger
     */
    public function __construct(
        Json $json,
        ScopeConfigInterface $scopeConfig,
        WebapiRequest $request,
        EncryptorInterface $encryptor,
        Logger $logger
    ) {
        $this->_json = $json;
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_encryptor = $encryptor;
        $this->_logger = $logger;
    }

    /**
     * Request Post data
     *
     * @return mixed
     */
    public function getRequest()
    {
        $requestData = $this->_request->getRequestData();
        if ($requestData) {
            $this->_request->setParams(array_merge(
                (array)$this->_request->getParams(),
                (array)$requestData
            ));
        }
        return $this->_request;
    }

    /**
     * Get Yotpo Api key from config
     *
     * @return string|null
     */
    public function getYotpoApiKey()
    {
        $apiKey = $this->getKey(self::XML_PATH_SWELL_API_KEY);
        return ($apiKey)? $this->_encryptor->decrypt($apiKey) : null;
    }

    /**
     * Getting the storeConfigValues
     *
     * @param string $key
     * @param null|int $storeId
     * @return mixed
     */
    public function getKey($key, $storeId = null)
    {
        return $this->_scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checking the api secret and configuration secret value is matching or not
     *
     * @return bool
     */
    public function isAuthorized()
    {
        $yotpoConfigApiKey = $this->getYotpoApiKey();
        $requestSecretKey = $this->getRequest()->getParam('secret');
        if ($yotpoConfigApiKey == $requestSecretKey) {
            return true;
        }
        return false;
    }

    /**
     * Json encode the data
     *
     * @param array $data
     * @return mixed
     */
    public function jsonSerailize($data)
    {
        return $this->_json->serialize($data);
    }

    /**
     * Json decode the data
     *
     * @param string $data
     * @return mixed
     */
    public function jsonUnserialize($data)
    {
        return $this->_json->unserialize($data);
    }
}
