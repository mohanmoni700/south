<?php

namespace Avalara\Excise\Api\Rest;

use Avalara\Excise\Model\Queue;
use Magento\Framework\DataObject;
use Avalara\Excise\Api\RestInterface;

interface TransactionInterface extends RestInterface
{
    /**
     * Perform REST request to get companies associated with the account
     *
     * @param Queue $queue
     * @param string $userTranId
     * @param int $storeId
     * @param string $scopeType
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    public function transactionsCommit(
        $queue,
        $userTranId,
        $storeId,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
