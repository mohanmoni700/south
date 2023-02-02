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

namespace Magedelight\Subscribenow\Block\Checkout\Onepage;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociatedOrders;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Subscription Order Summary
 *
 * cart summary block in `checkout_onepage_success.xml`
 */
class Success extends Template
{

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    
    /**
     * @var AssociatedOrders
     */
    private $associatedOrdersFactory;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param SubscriptionHelper $subscriptionHelper
     * @param AssociatedOrders $associatedOrdersFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        SubscriptionHelper $subscriptionHelper,
        AssociatedOrders $associatedOrdersFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->associatedOrdersFactory = $associatedOrdersFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * @return boolean
     */
    public function showBlock()
    {
        return $this->subscriptionHelper->isModuleEnable();
    }
    
    /**
     * @return object|null
     */
    public function getSubscriptionProfilesIds()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getRealOrderId();
        
        $collection = $this->associatedOrdersFactory->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->getSelect()->join(
            ['subscriber' => $collection->getTable('md_subscribenow_product_subscribers')],
            'subscriber.subscription_id = main_table.subscription_id',
            ['subscription_id','profile_id']
        );
        
        return $collection;
    }
    
    /**
     * @param int $id Subscription ID
     * @return string
     */
    public function getSubscriptionUrl($id)
    {
        return $this->getUrl('subscribenow/account/view/', ['id' => $id]);
    }
}
