<?php

namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\TransactionInterface;
use Avalara\Excise\Framework\Rest;
use Avalara\Excise\Logger\ExciseLogger;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;

/**
 * @codeCoverageIgnore
 */
class Transaction extends Rest implements TransactionInterface
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
     * @param \Avalara\Excise\Model\Queue $queue
     * @param string $userTranId
     * @param int $storeId
     * @param string $scopeType
     * @return array|\Magento\Framework\DataObject[]|mixed
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    public function transactionsCommit(
        $queue,
        $userTranId,
        $storeId,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        $client = $this->getClient(null, $storeId, $scopeType);

        $clientResult = json_encode("{}");
        if ($queue->hasData('store_id')) {
            try {
                $companyId = $this->config->getExciseCompanyId();
                $client = $this->setAuthentication($queue->getData('store_id'), $scopeType, $client);
                $clientResult = $client->commitTransaction(
                    $companyId,
                    $userTranId
                );
            } catch (\GuzzleHttp\Exception\RequestException $clientException) {
                // code to add CEP logs for exception
                try {
                    $functionName = "transactionsCommit";
                    $operationName = "Framework_Interaction_Rest_Transaction_Commit";
                    $source = "avatax_commit";
                    // @codeCoverageIgnoreStart
                    $this->loggerapi->logDebugMessage(
                        $functionName,
                        $operationName,
                        $clientException,
                        $source,
                        $storeId,
                        $scopeType
                    );
                    // @codeCoverageIgnoreEnd
                } catch (\Exception $e) {
                    //do nothing
                }
                // end of code to add CEP logs for exception
                $this->handleException($clientException);
            } catch (\Exception $exp) {
                // code to add CEP logs for exception
                try {
                    $functionName = "transactionsCommit";
                    $operationName = "Framework_Interaction_Rest_Transaction_Commit";
                    $exceptionObj = $exp;
                    $source = "avatax_commit";
                    // @codeCoverageIgnoreStart
                    $this->loggerapi->logDebugMessage(
                        $functionName,
                        $operationName,
                        $exceptionObj,
                        $source,
                        $storeId,
                        $scopeType
                    );
                    // @codeCoverageIgnoreEnd
                } catch (\Exception $e) {
                    //do nothing
                }
            // end of code to add CEP logs for exception
                $this->handleException($exp);
            }
        }

        return $this->formatResult($clientResult);
    }
}
