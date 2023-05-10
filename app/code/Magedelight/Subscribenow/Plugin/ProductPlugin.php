<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magedelight\Subscribenow\Model\Subscription;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;

class ProductPlugin
{
    private $subscription;
    /**
     * @var Data
     */
    private $subscriptionHelper;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var Http
     */
    private $http;

    /**
     * ProductPlugin constructor.
     * @param Subscription $subscription
     * @param Data $subscriptionHelper
     * @param Session $customerSession
     */
    public function __construct(
        Subscription $subscription,
        Data $subscriptionHelper,
        Http $http,
        Session $customerSession
    ) {
        $this->subscription = $subscription;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->customerSession = $customerSession;
        $this->http = $http;
    }

    /**
     * Set Final Price Product Detail
     *
     * @param object $productx
     * @param object $result
     *
     * @return float
     */
    public function afterGetFinalPrice($product, $result)
    {
        if (!$product->getTypeInstance() instanceof Type) {
            $result = $this->subscription->getFinalPrice($product, $result);
        }
        return $result;
    }

    /**
     * @param Product $product
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * Removing Add To Cart Subscription Product for Not Allowed Customer
     */
    public function afterIsSaleable(Product $product, $result)
    {
        if ($this->subscriptionHelper->isModuleEnable()) {
            if ($product->getIsSubscription() && $product->getSubscriptionType() == PurchaseOption::SUBSCRIPTION) {
                if ($this->http->getFullActionName() != 'catalog_product_view') {
                    $currentCustomerGroupId = $this->customerSession->getCustomerGroupId();
                    $allowedCustomerGroupIds = $this->subscriptionHelper->getAllowedCustomerGroups();
                    if($this->subscriptionHelper->isAllowToAddtoCart()){
                        $guestid = 0;
                        array_push($allowedCustomerGroupIds,$guestid);
                    }
                    if (!in_array($currentCustomerGroupId, $allowedCustomerGroupIds)) {
                        return [];
                    }
                }
                if (!$product->getHasOptions()) {
                    $currentCustomerGroupId = $this->customerSession->getCustomerGroupId();
                    $allowedCustomerGroupIds = $this->subscriptionHelper->getAllowedCustomerGroups();
                    if($this->subscriptionHelper->isAllowToAddtoCart()){
                        $guestid = 0;
                        array_push($allowedCustomerGroupIds,$guestid);
                    }
                    if (!in_array($currentCustomerGroupId, $allowedCustomerGroupIds)) {
                        return [];
                    }
                }
            }
        }
        return $result;
    }
}
