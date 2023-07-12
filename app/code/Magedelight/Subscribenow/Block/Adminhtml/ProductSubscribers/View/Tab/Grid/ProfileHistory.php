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
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscriptionHistory\CollectionFactory;

class ProfileHistory extends Extended
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
     * Constructor
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscribenow_profilehistory');
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
            ->addFieldToFilter('subscription_id', $subscriptionId)
            ->setOrder('hid', 'desc');
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * @return array
     */
    private function getModifyArray()
    {
        return [
            "0" => __('CRON'),
            "1" => __('Admin'),
            "2" => __('Customer')
        ];
    }
    
    /**
     * Add columns to Grid.
     * @return $this
     * @SuppressWarnings(PHPMagedelight.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'modify_by',
            [
                'header' => __('Modify By'),
                'index' => 'modify_by',
                'type' => 'options',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
                'options' => $this->getModifyArray(),
            ]
        );

        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'index' => 'comment',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Modify On'),
                'sortable' => true,
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px',
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
        $getGridUrl = $this->getUrl('*/*/profilehistory', ['_current' => true]);
        return $gridUrl ? $gridUrl : $getGridUrl;
    }
}
