<?php

namespace HookahShisha\YotpoLoyalty\Api;

use HookahShisha\YotpoLoyalty\Api\Data\CustomCouponGenerationSpecInterface;

/**
 * Coupon management interface
 */
interface CreateCouponManagementInterface
{

    /**
     * Generate coupon for a rule
     *
     * @return CustomCouponGenerationSpecInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function postCreateCustomCoupon();
}
