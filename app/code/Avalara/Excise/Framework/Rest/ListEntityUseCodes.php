<?php
/**
 * Avalara_Excise
 *
 * @copyright  Copyright (c) 2021 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\ListEntityUseCodesInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest;
use Magento\Framework\DataObject;
use Avalara\Excise\Logger\ExciseLogger;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Fetch entity use codes from the API
 * @codeCoverageIgnore
 */
class ListEntityUseCodes extends Rest implements ListEntityUseCodesInterface
{
     /**
     * @param ExciseClient $exciseClient
     *
     */
     public function __construct(
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        ConfigHelper $config,
        AvalaraClientWrapperFactory $clientWrapperFactory,
        Rest $exciseClient,
        ExciseLogger $loggerapi
    ) {
        $this->exciseClient = $exciseClient;
        $this->loggerapi = $loggerapi;
        $this->logger = $logger;
        parent::__construct($logger, $dataObjectFactory, $config, $clientWrapperFactory);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityUseCodes(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $accountNumber = $this->config->getAvaTaxAccountNumber($scopeId, $scopeType);
        try {
            $client = $this->getClient($type, $scopeId, $scopeType);
            $client = $this->setAvataxCredentials($scopeId, $scopeType, $client);
            $resultArray = $this->getEntityUseCodesWithSecurity($client, $accountNumber, $request, $scopeId, $scopeType);
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException);
        }
        return $resultArray;
    }

    /**
     * [getEntityUseCodesWithSecurity description]
     *
     * @return  [type]  [return description]
     */
    public function getEntityUseCodesWithSecurity(
        $client,
        $accountNumber,
        $request = null,
        $scopeId = null,
        $scopeType = null
    ) {
        $currentMode = $this->config->getCurrentModeString($scopeId, $scopeType);
        $isSandbox = true;
        if ($currentMode == Constants::API_MODE_PROD) {
            $isSandbox = false;
        }
        $this->isSandboxMode = $isSandbox;
        // Override security credentials with custom ones
        $client = $this->setAvataxCredentials($scopeId, $scopeType, $client);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);

        return $this->getEntityUseCodesList($client, $request, $scopeId, $scopeType);
    }

    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null  $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    protected function getEntityUseCodesList($client, $request = null, $scopeId = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryEntityUseCodes(
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // code to add CEP logs for exception
            try {
                $functionName = "getEntityUseFromAvalarAccount";
                $operationName = "Framework_Interaction_Rest_Entityusecode";
                $source = "avatax_entityusecodes";  
                // @codeCoverageIgnoreStart
                $this->loggerapi->logDebugMessage(
                    $functionName,
                    $operationName,
                    $clientException,
                    $source,
                    $scopeId,
                    $scopeType
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            $this->handleException($clientException);
        } catch (\Throwable $exception) {
            // code to add CEP logs for exception
            try {
                $functionName = "getEntityUseFromAvalarAccount";
                $operationName = "Framework_Interaction_Rest_Entityusecode";
                $source = "avatax_entityusecodes";   
                // @codeCoverageIgnoreStart
                $this->loggerapi->logDebugMessage(
                    $functionName,
                    $operationName,
                    $exception,
                    $source,
                    $scopeId,
                    $scopeType
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            throw $exception;
        }

        if (is_object($this->formatResult($clientResult))) {
            return $this->formatResult($clientResult)->getData('value');
        }
         return [];
    }   

    /**
     * @param string          $accountNumber
     * @param string          $password
     * @param DataObject|null $request
     * @param bool|null       $isProduction
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvataxConnectionException
     */
    public function getEntityUseCodesListWithSecurity(
        $type,
        $accountNumber,
        $password,
        $request = null,
        $isSandbox = null,
        $scopeId = null,
        $scopeType = null
    ) {
        $this->isSandboxMode = $isSandbox;
        // Override security credentials with custom ones
        $this->setCredentials($accountNumber, $password);
        $client = $this->getClient($type, $scopeId, $scopeType);
        $client->withCatchExceptions(false);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);
        return $this->getEntityUseCodesList($client, $request, $scopeId, $scopeType);
    } 

}
