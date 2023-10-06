<?php

declare(strict_types=1);

namespace Alfakher\MultipleCoupons\Plugin\Helper;

use Yotpo\Loyalty\Helper\Schema;

class YotpoSchema
{
    /**
     * @inheritdoc
     */
    public function afterOrderSchemaPrepare(
        Schema $subject,
        $result
    ) {
        $coupons = $result['coupon_code'] ?? '';
        $couponsExploded = explode(
            ",",
            implode(',', explode(";", $coupons))
        );
        $loyaltyCoupon = array_filter($couponsExploded, function ($coupon) {
            return str_contains($coupon, 'loyalty');
        });
        if (count($loyaltyCoupon)) {
            $result['coupon_code'] = reset($loyaltyCoupon);
        }
        return $result;
    }
}
