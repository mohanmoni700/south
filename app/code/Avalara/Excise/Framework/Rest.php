<?php

namespace Avalara\Excise\Framework;

use Avalara\Excise\Api\RestInterface;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Avalara\Excise\Framework\AvalaraClientWrapper;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;

/**
 * Implements the inerface class
 */
class Rest implements RestInterface
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var AvalaraClientWrapperFactory
     */
    protected $clientFactory;

    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var string
     */
    private $licenseKey;

    /**
     * @var string
     */
    private $apiType;

    /**
     * @var int
     */
    protected $isSandboxMode = null;

    /**
     * @param LoggerInterface   $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param Config $config
     * @param AvalaraClientWrapperFactory $clientWrapperFactory
     */
    public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ConfigHelper $config,
        AvalaraClientWrapperFactory $clientWrapperFactory
    ) {
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->config = $config;
        $this->clientFactory = $clientWrapperFactory;
    }

    /**
     * Generate cache key
     *
     * @param string   $type
     * @param string   $scopeType
     * @param int|null $scopeId
     *
     * @return string
     */
    protected function getClientCacheKey($type, $scopeType, $scopeId = null)
    {
        $cacheKey = $this->config->getCurrentModeString($scopeId, $scopeType);

        if ($scopeId !== null) {
            $cacheKey .= "-{$scopeId}";
        }

        return "{$cacheKey}-{$scopeType}-{$type}";
    }

    /**
     * create tax transaction
     *
     * @param int $storeId
     * @param array $payload
     * @param \Magento\Store\Model\ScopeInterface::SCOPE_STORE $scopeType
     * @return string
     */
    public function createExciseTaxTransaction(
        $storeId = null,
        $payload = [],
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $companyId = $this->config->getExciseCompanyId($storeId);
        
        $client = $this->getClient()->withCatchExceptions(false);
        $client = $this->setAuthentication($storeId, $scopeType, $client);

        $result = $client->createTaxTransaction($companyId, $payload);

        return $result;
    }

    /**
     * Get an Avalara REST API client object
     *
     * @param null|string     $type
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return AvalaraClientWrapper
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        if ($type === null) {
            $type = Constants::EXCISE_API;
        }

        $this->apiType = $type;

        $cacheKey = $this->getClientCacheKey($this->apiType, $scopeType, $scopeId);

        if (!isset($this->clients[$cacheKey])) {
            /** @var AvalaraClientWrapper $avalaraClient */
            $avalaraClient = $this->clientFactory->create(
                [
                    'config' => $this->config,
                    'store' => $scopeId,
                    'scope' => $scopeType,
                    'type' => $this->apiType,
                    'mode' => $this->isSandboxMode
                ]
            );

            $this->clients[$cacheKey] = $avalaraClient;
        }

        return $this->clients[$cacheKey];
    }

    /**
     * Ping Avalara REST service to verify connection/authentication
     *
     * @param string          $accountNo
     * @param string          $license
     * @param string|null     $mode
     * @param null|string     $type
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return bool
     * @throws AvalaraConnectionException
     * @throws \InvalidArgumentException
     */
    public function ping(
        $accountNo = null,
        $license = null,
        $mode = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $result = $path = null;

        if ($mode !== null) {
            $this->isSandboxMode = $mode;
        }

        if ($type == Constants::AVALARA_API) {
            $path = Constants::API_V2_AVATAX_PING_ENDPOINT;
        }
        
        try {
            if (!empty($accountNo) && !empty($license)) {
                $this->setCredentials($accountNo, $license);
            }
            $client = $this->getClient($type, $scopeId, $scopeType)
                ->withCatchExceptions(false);
            $client = $this->setAuthentication($scopeId, $scopeType, $client);
            $result = $client->ping($path);
        } catch (\GuzzleHttp\Exception\RequestException $requestException) {
            $this->handleException($requestException);
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            $this->handleException($clientException);
        }

        $authenticated = false;
        if ($result) {
            $authenticated = $result->authenticated;
        }
        return $authenticated;
    }

    /**
     * Set authentication credentials
     *
     * @param string $license
     * @param string $accountNo
     * @return void
     */
    protected function setCredentials($accountNo, $license)
    {
        $this->accountNumber = $accountNo;
        $this->licenseKey = $license;
    }

    /**
     * Set authentication headers
     *
     * @param null|int|string $scopeId
     * @param string $scopeType
     * @param AvalaraClientWrapper $avalaraClient
     * @return AvalaraClientWrapper
     */
    protected function setAuthentication($scopeId, $scopeType, $avalaraClient)
    {
        if ($this->apiType == Constants::EXCISE_API) {
            return $this->setExciseCredentials($scopeId, $scopeType, $avalaraClient);
        }
        
        return $this->setAvataxCredentials($scopeId, $scopeType, $avalaraClient);
    }

    /**
     * Set authentication headers for Excise API
     *
     * @param null|int|string $scopeId
     * @param string $scopeType
     * @param AvalaraClientWrapper $avalaraClient
     * @return AvalaraClientWrapper
     */
    protected function setExciseCredentials($scopeId, $scopeType, $avalaraClient)
    {
        $acctNo = $this->accountNumber;
        $key = $this->licenseKey;
        if (empty($acctNo) || empty($key)) {
            $acctNo = $this->config->getExciseAccountNumber($scopeId, $scopeType);
            $key = $this->config->getExciseLicenseKey($scopeId, $scopeType);
        }
        $avalaraClient->withBasicToken($acctNo, $key);
        return $avalaraClient;
    }

    /**
     * Set authentication headers for AvaTax API
     *
     * @param null|int|string $scopeId
     * @param string $scopeType
     * @param AvalaraClientWrapper $avalaraClient
     * @return AvalaraClientWrapper
     */
    protected function setAvataxCredentials($scopeId, $scopeType, $avalaraClient)
    {
        $acctNo = $this->accountNumber;
        $key = $this->licenseKey;
        if (empty($acctNo) || empty($key)) {
            $acctNo = $this->config->getAvaTaxAccountNumber($scopeId, $scopeType);
            $key = $this->config->getAvaTaxLicenseKey($scopeId, $scopeType);
        }
        $avalaraClient->withBasicToken($acctNo, $key);
        return $avalaraClient;
    }

    /**
     * @todo - Handle Exception and refactor
     * @param \GuzzleHttp\Exception\ClientException|\Exception $exception
     * @param DataObject|null                                  $request
     * @codeCoverageIgnore
     * @throws AvalaraConnectionException
     */
    protected function handleException($exception, $request = null, $logLevel = LOG_ERR)
    {
        $requestLogData = $request !== null ? var_export($request->getData(), true) : null;
        if ($request == null) {
            $request = $this->dataObjectFactory->create();
        }
        $logMessage = __('Avalara connection error: %1', $exception->getMessage());
        $logContext = ['request' => $requestLogData];

        if ($exception instanceof \GuzzleHttp\Exception\RequestException) {
            $requestUrl = '[' . (string)$exception->getRequest()->getMethod() . '] ' . (string)$exception->getRequest()
                ->getUri();
            $requestHeaders = json_encode(
                $exception->getRequest()->getHeaders(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
            $requestBody = json_decode((string)$exception->getRequest()->getBody(), true);
            $responseBody = null;
            $response = $exception->getResponse();

            if ($response !== null) {
                $responseBody = (string)$response->getBody();
                $response = json_decode($responseBody, true);
            }

            // If we have no body, use the request data as the body
            if ($requestBody !== null && (!is_array($requestBody) || !empty($requestBody))) {
                $responseBody = json_encode($requestBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            $logMessage = __('Response from Avalar indicated non-specific error: %1', $exception->getMessage());
            $logContext['request'] = var_export(
                [
                    'url' => $requestUrl,
                    'headers' => $requestHeaders,
                    'body' => $responseBody ?: $request->getData()
                ],
                true
            );

            if ($response !== null) {
                if (!empty($response['error']) && !empty($response['error']['details'])) {
                    try {
                        $logMessage = __(
                            'Avalara connection error: %1',
                            trim(
                                array_reduce(
                                    $response['error']['details'],
                                    function ($error, $detail) {
                                        if (isset($detail['severity']) && $detail['severity'] !== 'Exception'
                                                && $detail['severity'] !== 'Error') {
                                            return $error;
                                        }

                                        return $error . ' ' . $detail['description'];
                                    },
                                    ''
                                )
                            )
                        );
                    } catch (\Exception $ex) {
                        $logMessage = __(
                            'Avalara connection error: %1',
                            $ex->getMessage()
                        );
                        // code to add CEP logs for exception
                        try {
                            $functionName = __METHOD__;
                            $operationName = get_class($this); 
                            // @codeCoverageIgnoreStart               
                            $this->logger->logDebugMessage(
                                $functionName,
                                $operationName,
                                $ex
                            );
                            // @codeCoverageIgnoreEnd
                        } catch (\Exception $e) {
                            //do nothing
                        }
                        // end of code to add CEP logs for exception
                    }
                } elseif (!empty($response['title'])) {
                    $logMessage = __(
                        'Avalara connection error: %1',
                        $response['title']
                    );
                }

                $logContext['result'] = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }

        $logMethod = 'error';

        switch ($logLevel) {
            case LOG_DEBUG:
                $logMethod = 'debug';
                break;
            case LOG_WARNING:
                $logMethod = 'warning';
                break;
            case LOG_NOTICE:
                $logMethod = 'notice';
                break;
            case LOG_INFO:
                $logMethod = 'info';
                break;
            case LOG_ERR:
            default:
                $logMethod = 'error';
                break;
        }
        $this->logger->$logMethod($logMessage, $logContext);
        throw new AvalaraConnectionException($logMessage, $exception);
    }

    /**
     * Convert a simple object to a data object
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function formatResult($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = $this->formatResult($subValue);
            }
        } elseif (is_object($value)) {
            $valueObj = $this->dataObjectFactory->create();
            foreach ($value as $key => $subValue) {
                $methodName = 'set' . ucfirst($key);
                call_user_func([$valueObj, $methodName], $this->formatResult($subValue));
            }
            $value = $valueObj;
        }

        return $value;
    }

    /**
     * Returns a data object
     *
     * @return DataObject
     */
    public function getDataObject()
    {
        return $this->dataObjectFactory->create();
    }
}
