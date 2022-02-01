<?php

namespace HookahShisha\YotpoLoyalty\Api;

interface DeleteCouponManagementInterface
{
    /**
     * Delete custom coupon by id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function postDeleteCustomCoupon();
}
