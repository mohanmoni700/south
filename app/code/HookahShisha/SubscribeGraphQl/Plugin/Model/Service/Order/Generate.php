<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Service\Order;

use HookahShisha\SubscribeGraphQl\Model\Storage;
use Magedelight\Subscribenow\Model\Service\Order\Generate as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;

class Generate
{

    const FREE_SHIPPING_METHOD_CODE = 'freeshipping_freeshipping';

    private Storage $storage;
    private ScopeConfigInterface $scopeConfig;
    private SubscribeHelper $subscribeHelper;

    /**
     * @param Storage $storage
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscribeHelper $subscribeHelper
     */
    public function __construct(
        Storage $storage,
        ScopeConfigInterface $scopeConfig,
        SubscribeHelper $subscribeHelper
    )
    {
        $this->storage = $storage;
        $this->scopeConfig = $scopeConfig;
        $this->subscribeHelper = $subscribeHelper;
    }

    public function beforeGenerateOrder(Subject $subject)
    {
        $this->storage->set('is_subscription_recurring_order', true);
        return [];
    }


    public function aroundAddProductToCart(Subject $subject, callable $proceed, $cart)
    {
        $subscription = $subject->getProfile();
        $billingAmount = floatval($subscription->getData('billing_amount'));
        $discountAmount = floatval($subscription->getData('discount_amount'));
        $total = $billingAmount + $discountAmount;
        $discountRate = $discountAmount/$total;
        $this->storage->set('subscribe_order_product_discount_rate', $discountRate);


        $quoteItem = $cart->addProduct($subject->getProduct(), $subject->getBuyRequest());

        if (!is_object($quoteItem)) {
            throw new LocalizedException(__($quoteItem));
        }

        $quoteItem->setSubscriptionOrderGenerate(1);

        if ($subject->isProfileInTrialPeriod() && false) {
            $quoteItem->setName(__('Trial ') . $subject->getProduct()->getName());
            $quoteItem->setCustomPrice(0);
            $quoteItem->setOriginalCustomPrice(0);
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $subject->getProduct();
        if ($discountRate && $product->getTypeId() === 'simple') {
            $productPrice = $product->getFinalPrice();
            $discount = $productPrice * $discountRate;
            $finalPrice = $productPrice - $discount;
            $quoteItem->setCustomPrice($finalPrice); // showing discounted price
            $quoteItem->setOriginalCustomPrice($finalPrice); // setting product subtotal
        }
    }

    public function afterSetShippingMethod(Subject $subject, $result, $cart)
    {
        $isSubscriptionRecurringOrder = $this->storage->get('is_subscription_recurring_order');
        $isFreeShippingEnabledForSubscriptionOrders = $this->scopeConfig->getValue(
            'md_subscribenow/shipping/free_shipping_subscription'
        );
        $autoSelect = $this->subscribeHelper->isAutoSelectShippingMethod();
        if ($isSubscriptionRecurringOrder && !$autoSelect  && $isFreeShippingEnabledForSubscriptionOrders) {
            $cart->getShippingAddress()
                ->setShippingMethod(static::FREE_SHIPPING_METHOD_CODE)
                ->setCollectShippingRates(true);
        }
        $this->setIpAddress($subject, $cart);
        return $result;
    }

    protected function setIpAddress(Subject $subject, $cart)
    {
        /** @var \Magento\Quote\Model\Quote $cart */
        $subscription = $subject->getProfile();
        $cart->setRemoteIp($subscription->getData('ip_address'));
    }
}
