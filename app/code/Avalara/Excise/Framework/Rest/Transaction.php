<?php

namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\TransactionInterface;
use Avalara\Excise\Framework\Rest;

class Transaction extends Rest implements TransactionInterface
{
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
                $this->handleException($clientException);
            } catch (\Exception $exp) {
                $this->handleException($exp);
            }
        }

        return $this->formatResult($clientResult);
    }
}
