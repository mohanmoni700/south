<?php

namespace Avalara\Excise\Model;

use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest\Transaction;
use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Model\ResourceModel\Queue\CollectionFactory;
use Avalara\Excise\Model\Queue;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceFactory;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use \Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Avalara\Excise\Model\ProcessTaxQuote;

/**
 * @codeCoverageIgnore
 */
class QueueTask
{
    protected $logger;
    protected $queueCollectionFactory;
    protected $transaction;
    protected $queueFactory;
    protected $invoiceFactory;
    protected $creditmemoFactory;
    protected $creditmemoInterface;
    protected $notifierPool;

    const COMMIT_ERROR_STATUS = 'Errors found';
    const ENTITY_TYPE_CODE_INVOICE = 'invoice';
    const ENTITY_TYPE_CODE_CREDITMEMO = 'creditmemo';

    const STATUS_SAVED = 'Saved';
    const STATUS_PENDING = 'Pending';
    const STATUS_ERROR = 'Error';
    const STATUS_COMMITTED = 'Committed';
    const STATUS_PROGRESS = 'In Progress';

    
    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var Avalara\Excise\Model\ProcessTaxQuote
     */
    protected $processTaxQuote;

    /**
     * Undocumented function
     *
     * @param ExciseLogger $logger
     * @param CollectionFactory $queueCollectionFactory
     * @param Queue $queue
     * @param InvoiceFactory $invoiceFactory
     * @param CreditmemoFactory $creditmemoFactory
     * @param Transaction $transaction
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param CreditmemoRepositoryInterface $creditmemoInterface
     * @param \Avalara\Excise\Model\ProcessTaxQuote $processTaxQuote
     * @param NotifierPool $notifierPool
     */
    public function __construct(
        ExciseLogger $logger,
        CollectionFactory $queueCollectionFactory,
        Queue $queue,
        InvoiceFactory $invoiceFactory,
        CreditmemoFactory $creditmemoFactory,
        Transaction $transaction,
        ExciseTaxConfig $exciseTaxConfig,
        CreditmemoRepositoryInterface $creditmemoInterface,
        \Avalara\Excise\Model\ProcessTaxQuote $processTaxQuote,
        NotifierPool $notifierPool
    ) {
        $this->processTaxQuote = $processTaxQuote;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->queue = $queue;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->logger = $logger;
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->creditmemoInterface = $creditmemoInterface;
        $this->transaction = $transaction;
        $this->notifierPool = $notifierPool;
    }

    public function transactionsQueueCommit(
        $incrementId = null,
        $type = null
    ) {
        /** @var $collection \Avalara\Excise\Model\ResourceModel\Queue\Collection */
        $collection = $this->queueCollectionFactory->create();
        if ($incrementId != null && $type != null) {
            $collection->addFieldToFilter('entity_type_code', $type)
                ->addFieldToFilter('increment_id', $incrementId);
        } else {
            $collection->addCreatedAtBeforeFilter(Constants::COMMIT_API_PICK_TIME)
                ->addFieldToFilter('attempts', ['lt' => Constants::COMMIT_TRANSACTION_MAX_ATTEMPTS]);
        }
        $queueData = $collection
            ->addFieldToFilter('queue_status', [self::STATUS_PENDING, self::STATUS_ERROR, self::STATUS_PROGRESS]);
        
        foreach ($queueData as $queue) {
            $getObject =  $this->getQueueObject(
                $queue->getIncrementId(),
                $queue->getEntityTypeCode(),
                $queue->getEntityId()
            );
            $getTaxForQueueObject = false;
            $getTaxForQueueObject = $this->processTaxQuote->getRecalculatedTax($getObject, $queue->getEntityTypeCode());
            $queueObj = $this->queue->load($queue->getQueueId());
            $attempts = $queueObj->getAttempts() + 1;
            $queueObj->setAttempts($attempts);
            $queueObj->save();

            if ($queueObj->getQueueStatus() != self::STATUS_PROGRESS) {
                $queueObj->setQueueStatus(self::STATUS_PROGRESS);
                $queueObj->save();
            }
            $storeId = $getObject->getStoreId();

            if ($getTaxForQueueObject == ProcessTaxQuote::APIPASS) {
                $exciseTaxResponseOrder = $getObject->getData('excise_tax_response_order');
                $exciseTaxResponseOrderData = json_decode((string)$exciseTaxResponseOrder, true);
                if (!empty($exciseTaxResponseOrderData) && (isset($exciseTaxResponseOrderData['UserTranId']))) {
                    $queueObj->setMessage('Un-Balanced');
                    if ($getObject->getTaxAmount() == $exciseTaxResponseOrderData['TotalTaxAmount']) {
                        $queueObj->setMessage('Balanced');
                    }
                    if ($this->exciseTaxConfig->getExciseTaxCommitStatus($storeId) == "1") {
                        $transactionCommitData = $this->transaction->transactionsCommit(
                            $queue,
                            $exciseTaxResponseOrderData['UserTranId'],
                            $storeId
                        );
                        try {
                            $queueObj->setQueueStatus(self::STATUS_ERROR);
                            if (is_object($transactionCommitData) && !empty($transactionCommitData->getUserTranId())) {
                                $tranStatus = !empty($transactionCommitData->getStatus())
                                    ? $transactionCommitData->getStatus() : '';
                                if ($tranStatus == "Success") {
                                    $queueObj->setQueueStatus(self::STATUS_COMMITTED);
                                } elseif ($tranStatus == "Errors found") {
                                    $errorMessage = !empty(
                                        $transactionCommitData->getTransactionErrors()[0]->getErrorMessage()
                                    ) ? $transactionCommitData->getTransactionErrors()[0]->getErrorMessage() : '';
                                    $queueObj->setQueueStatus(self::STATUS_ERROR);
                                    $queueObj->setMessage($errorMessage);
                                }
                            }
                        } catch (\Exception $exp) {
                            $queueObj->setQueueStatus(self::STATUS_ERROR);
                            $this->logger->critical('Server Error: ' . $exp->getMessage());
                            // code to add CEP logs for exception
                            try {
                                $functionName = __METHOD__;
                                $operationName = get_class($this); 
                                // @codeCoverageIgnoreStart               
                                $this->logger->logDebugMessage(
                                    $functionName,
                                    $operationName,
                                    $exp
                                );
                                // @codeCoverageIgnoreEnd
                            } catch (\Exception $e) {
                                //do nothing
                            }
                            // end of code to add CEP logs for exception
                        }
                    } else {
                        try {
                            $queueObj->setQueueStatus(self::STATUS_SAVED);
                        } catch (\Exception $exp) {
                            $queueObj->setQueueStatus(self::STATUS_ERROR);
                            $this->logger->critical('Server Error: ' . $exp->getMessage());
                            // code to add CEP logs for exception
                            try {
                                $functionName = __METHOD__;
                                $operationName = get_class($this); 
                                // @codeCoverageIgnoreStart               
                                $this->logger->logDebugMessage(
                                    $functionName,
                                    $operationName,
                                    $exp
                                );
                                // @codeCoverageIgnoreEnd
                            } catch (\Exception $e) {
                                //do nothing
                            }
                            // end of code to add CEP logs for exception
                        }
                    }
                }
            } elseif ($getTaxForQueueObject == ProcessTaxQuote::APIERROR) {
                $queueObj->setQueueStatus(self::COMMIT_ERROR_STATUS);
            }
            $queueObj->save();
        }
    }

    /**
     * @param $incrementId
     * @param $entityTypeCode
     */
    public function getQueueObject($incrementId, $entityTypeCode, $entityId)
    {
        $QueueObject = false;
        switch ($entityTypeCode) {
            case self::ENTITY_TYPE_CODE_INVOICE:
                $QueueObject = $this->getInvoiceOrderByIncrementId($incrementId);
                break;
            case self::ENTITY_TYPE_CODE_CREDITMEMO:
                $QueueObject = $this->getCreditMemoOrderByIncrementId($entityId);
                break;
        }
        return $QueueObject;
    }

    public function getInvoiceOrderByIncrementId($incrementId)
    {
        /**
         * @var $invoiceFactory Invoice
         */
        $invoiceFactory = $this->invoiceFactory->create();
        $invoiceFactory->loadByIncrementId($incrementId);
        return $invoiceFactory;
    }
    public function getCreditMemoOrderByIncrementId($entityId)
    {
        return $this->creditmemoInterface->get($entityId);
    }
}
