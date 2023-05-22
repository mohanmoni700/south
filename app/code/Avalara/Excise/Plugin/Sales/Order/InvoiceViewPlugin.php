<?php

namespace Avalara\Excise\Plugin\Sales\Order;

use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Model\QueueTask;
use Avalara\Excise\Model\ResourceModel\Queue\CollectionFactory;

class InvoiceViewPlugin
{
    /**
     * @var ExciseLogger
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * InvoiceViewPlugin constructor.
     * @param ExciseLogger $logger
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        ExciseLogger $logger,
        CollectionFactory $queueCollectionFactory
    ) {
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Invoice\View $view
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\Invoice\View $view)
    {
        $invoice = $view->getInvoice();
        if ($invoice) {
            /** @var $collection \Avalara\Excise\Model\ResourceModel\Queue\Collection */
            $collection = $this->queueCollectionFactory->create();
            $collection->addFieldToFilter('queue_status', [
                QueueTask::STATUS_PENDING,
                QueueTask::STATUS_ERROR
            ])
                ->addFieldToFilter('entity_type_code', QueueTask::ENTITY_TYPE_CODE_INVOICE)
                ->addFieldToFilter('entity_id', $invoice->getId());
            if ($collection->getSize()) {
                $url = $view->getUrl(
                    'excise/invoice/send',
                    [
                        'id' => $invoice->getId(),
                        'increment_id' => $invoice->getIncrementId(),
                        'type' => QueueTask::ENTITY_TYPE_CODE_INVOICE
                    ]
                );
                $view->addButton(
                    'invoice_excise',
                    [
                        'label' => __('Post To Avatax'),
                        'class' => 'excise_invoice',
                        'onclick' => 'setLocation(\'' . $url . '\')'
                    ]
                );
            }
        }
    }
}
