<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;
use Magento\Sales\Model\Order\Config;

class RelatedOrders extends Extended
{

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $orderModelConfig;

    /**
     * Constructor
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param Config $orderModelConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        Config $orderModelConfig,
        array $data = []
    ) {

        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->orderModelConfig = $orderModelConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscribenow_related_orders');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    private function getSubscription()
    {
        return $this->coreRegistry->registry('md_subscribenow_product_subscriber');
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $subscriptionId = $this->getSubscription()->getId();

        $collection->join(
            ['associate_order' => $collection->getTable('md_subscribenow_product_associated_orders')],
            "main_table.increment_id = associate_order.order_id AND associate_order.subscription_id = $subscriptionId",
            ['subscription_id']
        );
        
        $collection->setOrder('entity_id', 'DESC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add columns to Grid.
     * @return $this
     * @SuppressWarnings(PHPMagedelight.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order #'),
                'sortable' => true,
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'header' => __('Purchase Point'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'sortable' => true,
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => __('Bill to Name'),
                'index' => 'billing_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Grand Total(Base)'),
                'type' => 'currency',
                'currency' => 'base_currency_code',
                'index' => 'base_grand_total',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Grand Total(Purchased)'),
                'type' => 'currency',
                'currency' => 'order_currency_code',
                'index' => 'base_grand_total',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
                'options' => $this->orderModelConfig->getStatuses(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => 'sales/order/view'],
                        'field' => 'order_id',
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Rerieve Grid URL.
     */
    public function getGridUrl()
    {
        $gridUrl = $this->getData('grid_url');
        $getGridUrl = $this->getUrl('*/*/relatedorders', ['_current' => true]);
        return $gridUrl ? $gridUrl : $getGridUrl;
    }
}
