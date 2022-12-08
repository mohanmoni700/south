<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class EasyCredit extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_easycredit';
    protected $brand = 'RATENKAUF';
    protected $methodTitle = 'FRONTEND_PM_EASYCREDIT';
    protected $paymentType = 'PA';
    protected $logo = 'easycredit.png';
    protected $isServerToServer = true;
    protected $isCreatedOrderBeforePayment = false;
}
