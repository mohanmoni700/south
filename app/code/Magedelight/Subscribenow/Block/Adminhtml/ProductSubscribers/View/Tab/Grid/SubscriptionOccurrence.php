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
use Magedelight\Subscribenow\Model\ResourceModel\ProductOccurrence\CollectionFactory;
use Magento\Sales\Model\Order\Config;

class SubscriptionOccurrence extends Extended
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
        $this->setId('subscribenow_occurrence');
        $this->setDefaultSort('occurrence_id');
        $this->setDefaultDir('desc');
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
        $subscriptionId = $this->getSubscription()->getId();
        
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('subscription_id', $subscriptionId);
        
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
            'occurrence_id',
            [
                'header' => __('Occurrence ID'),
                'sortable' => true,
                'index' => 'occurrence_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'occurrence_date',
            [
                'header' => __('Occurrence Date'),
                'sortable' => true,
                'index' => 'occurrence_date',
                'type' => 'datetime',
                'width' => '100px',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );

        $this->addColumn(
            'order_id',
            [
                'header' => __('Order ID'),
                'index' => 'order_id',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'order_status',
            [
                'header' => __('Order Status'),
                'index' => 'order_status',
                'type' => 'options',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
                'options' => $this->orderModelConfig->getStatuses(),
            ]
        );

        $this->addColumn(
            'occurrence_order',
            [
                'header' => __('Occurrence Status'),
                'sortable' => true,
                'index' => 'occurrence_order',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
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
        $getGridUrl = $this->getUrl('*/*/subscriptionoccurrence', ['_current' => true]);
        return $gridUrl ? $gridUrl : $getGridUrl;
    }
}
