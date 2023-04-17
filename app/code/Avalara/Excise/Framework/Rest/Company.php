<?php

namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\CompanyInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;
use Avalara\Excise\Logger\ExciseLogger;


/**
 * @codeCoverageIgnore
 */
class Company extends Rest implements CompanyInterface
{
    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null  $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    /**
     * @var ExciseClient
     */
    protected $exciseClient;

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

    protected function getCompaniesFromAvalarAccount($client, $request = null, $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryAvataxCompanies(
                $request->getData('include'),
                $request->getData('filter'),
                $request->getData('top'),
                $request->getData('skip'),
                $request->getData('order_by')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // code to add CEP logs for exception
            try {
                $functionName = "getCompaniesFromAvalarAccount";
                $operationName = "Framework_Interaction_Rest_Company";
                $source = "avatax_companies"; 
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
            $this->handleException($clientException, $request);
        } catch (\Throwable $exception) {
            // code to add CEP logs for exception
            try {
                $functionName = "getCompaniesFromAvalarAccount";
                $operationName = "Framework_Interaction_Rest_Company";
                $source = "avatax_companies";
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

        return $this->formatResult($clientResult)->getData('value');
    }

    /**
     * @param \Avalara\Excise\Framework\AvalaraClientWrapper $client
     * @param DataObject|null $request
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    protected function getCompaniesFromExciseAccount($client, $request = null, $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        if ($request === null) {
            $request = $this->dataObjectFactory->create();
        }

        $clientResult = null;

        try {
            $clientResult = $client->queryExciseCompanies(
                $request->getData('effectiveDate')
            );
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            // code to add CEP logs for exception
            try {
                $functionName = "getCompaniesFromExciseAccount";
                $operationName = "Framework_Interaction_Rest_Company";
                $source = "excise_companies";
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
            $this->handleException($clientException, $request);
        } catch (\Throwable $exception) {
            // code to add CEP logs for exception
            try {
                $functionName = "getCompaniesFromExciseAccount";
                $operationName = "Framework_Interaction_Rest_Company";
                $source = "excise_companies";    
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
        
        if (is_array($clientResult)) {
            foreach ($clientResult as $idx => $res) {
                if ($res->IsActive != 1 || $res->HasAvaTaxExcise != 1) {
                    unset($clientResult[$idx]);
                }
            }
        }
        
        return $this->formatResult($clientResult);
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanies(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $client = $this->getClient($type, $scopeId, $scopeType);

        if ($type == Constants::AVALARA_API) {
            return $this->getCompaniesFromAvalarAccount($client, $request, $scopeId, $scopeType);
        }
        return $this->getCompaniesFromExciseAccount($client, $request, $scopeId, $scopeType);
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
    public function getCompaniesWithSecurity(
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
        $client = $this->getClient($type);
        $client->withCatchExceptions(false);
        $client = $this->setAuthentication($scopeId, $scopeType, $client);

        if ($type == Constants::AVALARA_API) {
            return $this->getCompaniesFromAvalarAccount($client, $request, $scopeId, $scopeType);
        }
        return $this->getCompaniesFromExciseAccount($client, $request, $scopeId, $scopeType);
    }
}
