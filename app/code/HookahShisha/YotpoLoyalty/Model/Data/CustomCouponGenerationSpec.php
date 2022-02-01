<?php

namespace HookahShisha\YotpoLoyalty\Model\Data;

use HookahShisha\YotpoLoyalty\Api\Data\CustomCouponGenerationSpecInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CouponGenerationSpec
 *
 */
class CustomCouponGenerationSpec extends AbstractModel implements CustomCouponGenerationSpecInterface
{
    /**
     * Get the id of the associated with the coupon
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * Set coupon id
     *
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }
}
