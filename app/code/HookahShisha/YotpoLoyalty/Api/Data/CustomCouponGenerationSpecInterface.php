<?php
namespace HookahShisha\YotpoLoyalty\Api\Data;

/**
 * CouponCouponGenerationSpecInterface for Yotpo
 */
interface CustomCouponGenerationSpecInterface
{

    public const KEY_ID = 'id';

    /**
     * Get the id of the associated with the coupon
     *
     * @return int
     */
    public function getId();

    /**
     * Set coupon id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);
}
