<?php

namespace Avalara\Excise\Plugin\Sales\Order;

use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Model\QueueTask;
use Avalara\Excise\Model\ResourceModel\Queue\CollectionFactory;

class CreditMemoViewPlugin
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
     * @param \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View $view
     * @return void
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View $view)
    {
        $creditMemo = $view->getCreditmemo();
        if ($creditMemo->getId()) {
            $collection = $this->queueCollectionFactory->create();
            $collection->addFieldToFilter('queue_status', [
                QueueTask::STATUS_PENDING,
                QueueTask::STATUS_ERROR
            ])
                ->addFieldToFilter('entity_type_code', QueueTask::ENTITY_TYPE_CODE_CREDITMEMO)
                ->addFieldToFilter('entity_id', $creditMemo->getId());

            if ($collection->getSize()) {
                $url = $view->getUrl(
                    'excise/invoice/send',
                    [
                        'id' => $creditMemo->getId(),
                        'increment_id' => $creditMemo->getIncrementId(),
                        'type' => QueueTask::ENTITY_TYPE_CODE_CREDITMEMO
                    ]
                );
                $view->addButton(
                    'creditmemo_excise',
                    [
                        'label' => __('Post To Avatax'),
                        'class' => 'excise_creditmemo',
                        'onclick' => 'setLocation(\'' . $url . '\')'
                    ]
                );
            }
        }
    }
}
