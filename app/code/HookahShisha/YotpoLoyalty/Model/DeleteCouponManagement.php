<?php

namespace HookahShisha\YotpoLoyalty\Model;

use HookahShisha\YotpoLoyalty\Api\DeleteCouponManagementInterface;
use Magento\Framework\Exception\LocalizedException;

class DeleteCouponManagement implements DeleteCouponManagementInterface
{

    /**
     * @var YotpoData
     */
    protected $_yotpoData;

    /**
     * DeleteCouponManagement constructor.
     * @param YotpoData $yotpoData
     */
    public function __construct(
        YotpoData $yotpoData
    ) {
        $this->_yotpoData = $yotpoData;
    }

    /**
     * @inheritdoc
     */
    public function postDeleteCustomCoupon()
    {
        if (!$this->_yotpoData->isAuthorized()) {
            throw new LocalizedException(
                __('Access Denied!')
            );
        }
        //Request Params:
        $couponId = $this->_yotpoData->getRequest()->getParam('id');
        if (!$couponId) {
            throw new LocalizedException(
                __('id` is a required field!')
            );
        }
        return true;
    }
}
