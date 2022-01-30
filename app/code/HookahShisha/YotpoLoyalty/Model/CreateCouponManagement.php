<?php

namespace HookahShisha\YotpoLoyalty\Model;

use HookahShisha\YotpoLoyalty\Api\CreateCouponManagementInterface;
use HookahShisha\YotpoLoyalty\Model\Data\CustomCouponGenerationSpecFactory;
use Magento\Framework\Exception\LocalizedException;

class CreateCouponManagement implements CreateCouponManagementInterface
{
    /**
     * @var Data\CustomCouponGenerationSpecFactory
     */

    protected $_customCouponGenerationSpecFactory;

    /**
     * @var YotpoData
     */
    protected $_yotpoData;

    /**
     * CreateCouponManagement constructor.
     * @param YotpoData $yotpoData
     * @param Data\CustomCouponGenerationSpecFactory $customCouponGenerationSpecFactory
     */
    public function __construct(
        YotpoData $yotpoData,
        CustomCouponGenerationSpecFactory $customCouponGenerationSpecFactory
    ) {
        $this->_yotpoData = $yotpoData;
        $this->_customCouponGenerationSpecFactory = $customCouponGenerationSpecFactory;
    }

    /**
     * @inheritdoc
     */
    public function postCreateCustomCoupon()
    {
        if (!$this->_yotpoData->isAuthorized()) {
            throw new LocalizedException(
                __('Access Denied!')
            );
        }
        $couponGenerator = $this->_customCouponGenerationSpecFactory->create();
        $couponGenerator->setId(2);
        return $couponGenerator;
    }
}
