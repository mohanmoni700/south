<?php
/**
 * Copyright (c) 2020 Magedelight Solution Pvt. Ltd.
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GPL 3.0
 * @link     http://www.magedelight.com/
 */

namespace Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Model\Source\DiscountType;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;

/**
 * Class DiscountService
 * Subscribe Now Discount Calculation
 *
 * @since 200.5.0
 * @package Magedelight\Subscribenow\Model\Service
 */
class DiscountService
{

    public function isSubscriptionOnly($product)
    {
        $isSubscription = $product->getIsSubscription();
        $subscriptionType = $product->getSubscriptionType();
        if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
            return $product;
        }

        return false;
    }

    public function calculateDiscount($price, $product)
    {
        $discountedPrice = $price;
        if ($product) {
            $discount = $product->getDiscountAmount();
            $type = $product->getDiscountType();
            if ($type == DiscountType::PERCENTAGE) {
                $percentageAmount = $price * ($discount / 100);
                $discountedPrice = $price - $percentageAmount;
            } else {
                $discountedPrice = $price - $discount;
            }
        }

        return max(0, $discountedPrice);
    }

    public function calculateDiscountByValue($price, $type, $discount)
    {
        if ($type == DiscountType::PERCENTAGE) {
            $percentageAmount = $price * ($discount / 100);
            $discountedPrice = $price - $percentageAmount;
        } else {
            $discountedPrice = $price - $discount;
        }

        return max(0, $discountedPrice);
    }
}
