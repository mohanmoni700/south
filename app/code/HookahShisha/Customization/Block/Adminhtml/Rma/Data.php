<?php

namespace HookahShisha\Customization\Block\Adminhtml\Rma;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Data extends Template
{
    /**
     * @var rmaFactory
     */
    protected $rmaFactory;
    /**
     * Construct
     *
     * @param \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaFactory
     * @param \Magento\Rma\Model\ResourceModel\Rma\Status\History\CollectionFactory $collectionFactory
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $rmaData
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaFactory,
        \Magento\Rma\Model\ResourceModel\Rma\Status\History\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $rmaData,
        Context $context,
        array $data = []
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->rmaData = $rmaData;
        parent::__construct($context, $data);
    }
    /**
     * Get Rma collection
     *
     * @param string $orderId
     * @return return entity id
     */
    public function getRmaCollection($orderId)
    {
        $collection = $this->rmaFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'order_id',
            $orderId
        )->getData();
        foreach ($collection as $val) {
            $entity_id[] = $val['entity_id'];
        }
        return $entity_id;
    }

    /**
     * Get comments
     *
     * @param string $id
     * @return \Magento\Rma\Model\ResourceModel\Rma\Status\History\Collection
     */
    public function getComments($id)
    {

        foreach ($this->getRmaCollection($id) as $val) {
            $collection = $this->_collectionFactory->create();
        }
        return $collection->addFieldToFilter('rma_entity_id', $this->getRmaCollection($id));
    }

    /**
     * Retrive Return id
     *
     * @param string $orderId
     * @return rmaModel
     */
    public function getReturnid($orderId)
    {
        $rmaModel = $this->rmaData->create();
        return $rmaModel->addFieldToFilter('order_id', $orderId);
    }
}
