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
 *
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;
use Magedelight\Subscribenow\Helper\Data;
use \Magento\Framework\App\ResourceConnection;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory;

class ProductSubscriptionGrid extends Extended
{

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $productSubscriber;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        Registry $coreRegistry,
        CollectionFactory $productSubscriber,
        Http $request,
        Data $helper,
        ResourceConnection $resource,
        $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->productSubscriber = $productSubscriber;
        $this->request = $request;
        $this->helper = $helper;
        $this->_resource = $resource;
        
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid parameters.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('md_product_subscription_customer');
        $this->setDefaultSort('subscription_id', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection.
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $customerId = $this->request->getParam('id');

        $collection = $this->productSubscriber->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $customerId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid.
     *
     * @return $this
     * @SuppressWarnings(PHPMagedelight.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addColumn('profile_id', [
            'header' => __('Profile Reference ID #'),
            'index' => 'profile_id',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name',
            ]);

        $this->addColumn('subscription_status', [
            'header' => __('Status'),
            'index' => 'subscription_status',
            'type' => 'options',
            'header_css_class' => 'col-type',
            'column_css_class' => 'col-type',
            'options' => $this->helper->getStatusLabel(),
            ]);

        $this->addColumn('subscriber_name', [
            'header' => __('Subscriber Name'),
            'index' => 'subscriber_name',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name',
            ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                ['header' => __('Subscription Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
            );
        }

        $this->addColumn('next_occurrence_date', [
            'header' => __('Next Occurrence Date'),
            'index' => 'next_occurrence_date',
            'filter' => false,
            'sortable' => false
            ]);


        $this->addColumn('product_name', [
            'header' => __('Product Name'),
            'index' => 'product_name',
            'filter' => false,
            'sortable' => false,
            'renderer' => '\Magedelight\Subscribenow\Block\Adminhtml\Customer\Edit\Tab\Renderer\ProductName'
            ]);

        $supportedPaymentMethod = [
            "cashondelivery" => __("Cash On Delivery"),
            "magedelight_cybersource" => __("Cybersource Payment"),
            "md_stripe_cards" => __("Stripe Payment"),
            "md_authorizecim" => __("Authorize.net CIM"),
            "free" => __("Zero Subtotal"),
            "braintree_cc_vault" => __("Stored Cards (Braintree)"),
            "braintree" => __("Braintree"),
            "payflowpro_cc_vault" => __("Stored Cards (Paypal Payflow)"),
            "payflowpro" => __("Paypal Payflow Pro"),
            "md_moneris" => __("Moneris Payment"),
            "md_monerisca" => __("Moneris Payment (Canada)"),
            "md_firstdata" => __("Firstdata Payment"),
            "adyen_cc" => __("Adyen Payment"),
            "ops_cc" => __("Ingenico ePayments Cc")
        ];
        $this->addColumn('payment_method_code', [
            'header' => __('Payment Method'),
            'index' => 'payment_method_code',
            'type' => 'options',
            'header_css_class' => 'col-type',
            'column_css_class' => 'col-type',
            'options' => $supportedPaymentMethod,
            ]);
        $this->addColumn(
            'created_at',
            [
            'header' => __('Created On'),
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
     * Rerieve grid URL.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url') ?
            $this->getData('grid_url') :
            $this->getUrl('subscribenow/productsubscribers/customersubscription', ['_current' => true]);
    }
}
