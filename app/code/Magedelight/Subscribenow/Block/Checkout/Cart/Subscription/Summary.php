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

namespace Magedelight\Subscribenow\Block\Checkout\Cart\Subscription;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;

/**
 * Subscription Summary
 *
 * it will worked on Subscribe Now Configuration
 * `product_subscription/enabled` settings
 * this will display subscription item name in
 * cart summary block in checkout_cart_index
 */
class Summary extends Template
{

    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;

    /**
     * @var SubscriptionService
     */
    private $subscriptionService;
    
    /**
     * Subscription constructor
     *
     * @param Context $context
     * @param SubscriptionHelper $subscriptionHelper
     * @param SubscriptionService $subscriptionService
     * @param array $data
     */
    public function __construct(
        Context $context,
        SubscriptionHelper $subscriptionHelper,
        SubscriptionService $subscriptionService,
        array $data = []
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionService = $subscriptionService;
        parent::__construct($context, $data);
    }
    
    /**
     * Summary Content
     * @return string
     */
    public function showBlock()
    {
        return $this->subscriptionHelper->isModuleEnable()
                && $this->subscriptionHelper->isShowCartSummaryBlock();
    }

    /**
     * Summary Heading
     * @return string
     */
    public function getSummaryTitle()
    {
        return $this->subscriptionHelper->getSummaryBlockTitle();
    }

    /**
     * Summary Content
     * @return string
     */
    public function getSummaryContent()
    {
        return $this->subscriptionHelper->getSummaryBlockContetnt();
    }
    
    /**
     * Get Checkout Items
     * @return mixed
     */
    public function getItems()
    {
        return $this->subscriptionHelper->getCurrentQuote()->getAllVisibleItems();
    }
    
    /**
     * Check is subscription item
     * @param boolean
     */
    private function isSubscription($item)
    {
        return $this->subscriptionService->isSubscribed($item);
    }
    
    /**
     * Get Subscription Items
     * @return array
     */
    public function getSubscriptionItems()
    {
        $subscriptionItems = [];
        $items = $this->getItems();
        foreach ($items as $item) {
            if ($this->isSubscription($item)) {
                $subscriptionItems[] = $item->getProduct()->getName();
            }
        }
        return $subscriptionItems;
    }

    /**
     * Check Subscription Item Available
     * @return boolean
     */
    public function hasSubscriptionItem()
    {
        if ($this->showBlock()) {
            $items = $this->getSubscriptionItems();
            if ($items && count($items)) {
                return true;
            }
        }
        return false;
    }
}
