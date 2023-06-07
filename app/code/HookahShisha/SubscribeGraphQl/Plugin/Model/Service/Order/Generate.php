<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Service\Order;

use HookahShisha\SubscribeGraphQl\Model\Storage;
use Magedelight\Subscribenow\Model\Service\Order\Generate as Subject;
use Magento\Framework\Exception\LocalizedException;

class Generate
{
    private Storage $storage;

    /**
     * @param Storage $storage
     */
    public function __construct(
        Storage $storage
    )
    {
        $this->storage = $storage;
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
}
