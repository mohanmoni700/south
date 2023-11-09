<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Magento\Bundle\Model\Product;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Subscription;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use HookahShisha\SubscribeGraphQl\Model\Storage;

/**
 * Subscribe now
 * Bundle Product Prices set in on order recurrence time
 * if dynamic price is no
 */
class Price
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * To calculate the final price
     *
     * @param Data $helper
     * @param Subscription $subscription
     * @param Storage $storage
     */
    public function __construct(
        Data         $helper,
        Subscription $subscription,
        Storage $storage
    ) {
        $this->subscription = $subscription;
        $this->helper = $helper;
        $this->storage = $storage;
    }

    /**
     * To get the Final Price
     *
     * @param $subject
     * @param $result
     * @param $bundleProduct
     * @param $selectionProduct
     * @param $bundleQty
     * @param $selectionQty
     * @param $multiplyQty
     * @param $takeTierPrice
     * @return float|int|mixed
     */
    public function afterGetSelectionFinalTotalPrice(
        $subject,
        $result,
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        $discountRate = floatval($this->storage->get('subscribe_order_product_discount_rate'));
        if ($discountRate) {
            $productPrice = $result;
            $discount = $productPrice * $discountRate;
            return $productPrice - $discount;
        }

        if ($bundleProduct->hasSkipValidateTrial() && $bundleProduct->getSkipValidateTrial()) {
            $bundleProduct->setSkipValidateTrial(1);
            $selectionProduct->setSkipValidateTrial(1);
        }

        return $this->calculateFinalPrice($bundleProduct, $selectionProduct, $result);
    }

    /**
     *To calculate the final price
     *
     * @param $bundleProduct
     * @param $selectionProduct
     * @param $result
     * @return float|int|mixed
     */
    private function calculateFinalPrice($bundleProduct, $selectionProduct, $result)
    {
        if ($bundleProduct->hasData('is_subscription_recurring_order')
            && $bundleProduct->getData('is_subscription_recurring_order')
            && $oldBundleOption = $bundleProduct->getData('subscription_bundle_option')
        ) {
            $selectProductOptionId = $selectionProduct->getOptionId();
            return $this->getBundlePrice(
                $selectProductOptionId,
                $oldBundleOption,
                $bundleProduct,
                $selectionProduct
            );
        } elseif (!$bundleProduct->getSkipBundleDiscount()
            && $this->subscription->isSubscriptionProduct($bundleProduct)
            && $bundleProduct->getData('subscription_type') == PurchaseOption::EITHER
        ) {
            return $this->subscription->getFinalPrice($bundleProduct, $result);
        }
        return $result;
    }

    /**
     * To get the bundle price
     *
     * @param $selectProductOptionId
     * @param $oldBundleOption
     * @param $bundleProduct
     * @param $selectionProduct
     * @return int|mixed
     */
    private function getBundlePrice(
        $selectProductOptionId,
        $oldBundleOption,
        $bundleProduct,
        $selectionProduct
    ) {
        if (in_array($selectProductOptionId, array_keys($oldBundleOption))) {
            $optionData = $oldBundleOption[$selectProductOptionId];

            if ($optionData && !empty($optionData['value'])
                && $optionDataValue = $optionData['value'][0]
            ) {
                $qty = !empty($optionDataValue['qty']) ? $optionDataValue['qty'] : 0;

                //Calculated the price based on the product discount
                $bundleDiscount = $bundleProduct->getDiscountAmount();
                $price = $selectionProduct->getFinalPrice();
                if (isset($bundleDiscount)) {
                    $price = $selectionProduct->getPrice() - $bundleDiscount;
                }

                if ($qty && $price) {
                    return max(0, $price);
                }
            }
        }
        return 0;
    }
}
