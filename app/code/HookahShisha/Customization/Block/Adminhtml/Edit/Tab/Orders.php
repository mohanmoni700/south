<?php

namespace HookahShisha\Customization\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Edit\Tab\Orders as BaseOrders;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order\Config as OrderConfig;

class Orders extends BaseOrders
{
    /**
     * @var OrderConfig
     */
    protected $orderConfig;

    /**
     * Orders constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Reorder $salesReorder
     * @param Registry $coreRegistry
     * @param OrderConfig $orderConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Reorder $salesReorder,
        Registry $coreRegistry,
        OrderConfig $orderConfig,
        array $data = []
    ) {

        $this->orderConfig = $orderConfig;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $salesReorder,
            $coreRegistry,
            $data
        );
    }

    /**
     * @inheirtDoc
     */
    public function setCollection($collection)
    {
        $collection->addFieldToSelect('status');
        parent::setCollection($collection);
    }

    /**
     * @inheirtDoc
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter('status', array(
            'header' => __('Status'),
            'index' => 'status',
            'type' => 'options',
            'width' => '70px',
            'options' => $this->orderConfig->getStatuses(),
        ), 'store_id');

        $this->sortColumnsByOrder();
        return $this;
    }
}
