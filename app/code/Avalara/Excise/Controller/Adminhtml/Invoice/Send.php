<?php

namespace Avalara\Excise\Controller\Adminhtml\Invoice;

use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Model\Queue;
use Avalara\Excise\Model\QueueTask;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Avalara\Excise\Model\ResourceModel\Queue\CollectionFactory;

/**
 * @codeCoverageIgnore
 */
class Send extends Action
{

    /**
     * @var ExciseLogger
     */
    protected $exciseLogger;
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var QueueTask
     */
    protected $queueTask;

    /**
     * @var CollectionFactory
     */
    protected $queueFactory;

    /**
     * Process constructor
     *
     * @param Context $context
     * @param Queue $queue
     * @param ExciseLogger $exciseLogger
     * @param QueueTask $queueTask
     */
    public function __construct(
        Context $context,
        Queue $queue,
        ExciseLogger $exciseLogger,
        QueueTask $queueTask,
        CollectionFactory $queueFactory
    ) {
        $this->queueTask = $queueTask;
        $this->queue = $queue;
        $this->exciseLogger = $exciseLogger;
        $this->queueFactory = $queueFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $txnId = (int)$this->getRequest()->getParam('id');
        $incrementId = $this->getRequest()->getParam('increment_id');
        $type = $this->getRequest()->getParam('type');
        $error = 1;
        $message = __(
            'Unable to post to Avatax. Check logs for details',
            $incrementId
        );
        try {
            $this->queueTask->transactionsQueueCommit(
                $incrementId,
                $type
            );
            $collection = $this->queueFactory->create();
            $collection->addFieldToFilter('entity_type_code', $type)
                ->addFieldToFilter('increment_id', $incrementId);
            if ($collection->getSize()) {
                $data = $collection->getFirstItem();
                if ($data->getQueueStatus() == QueueTask::STATUS_SAVED ||
                    $data->getQueueStatus() == QueueTask::STATUS_COMMITTED) {
                    $message = __(
                        'Transaction #%1 was successfully sent to Avalara.',
                        $incrementId
                    );
                    $error = 0;
                    $this->messageManager->addSuccessMessage($message);
                } else {
                    if ($data->getQueueStatus() == QueueTask::STATUS_ERROR) {
                        $message = __(
                            $data->getMessage(),
                            $incrementId
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $error = 1;
            $message = __(
                'Error encountered, '.$e->getMessage()
            );
        }

        if ($error == 1) {
            $this->messageManager->addErrorMessage($message);
        }

        // Redirect browser to log list page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath(
            'sales/invoice/view',
            ['invoice_id' => $txnId]
        );
        if ($type == QueueTask::ENTITY_TYPE_CODE_CREDITMEMO) {
            $resultRedirect->setPath(
                'sales/creditmemo/view',
                ['creditmemo_id' => $txnId]
            );
        }
        return $resultRedirect;
    }
}
