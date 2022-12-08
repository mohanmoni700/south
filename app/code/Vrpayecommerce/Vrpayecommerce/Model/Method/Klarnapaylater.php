<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class Klarnapaylater extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_klarnapaylater';
    protected $brand = 'KLARNA_INVOICE';
    protected $methodTitle = 'FRONTEND_PM_KLARNAPAYLATER';
    protected $paymentType = 'PA';
    protected $logo = 'klarnapaylater_en.png';
    protected $logoDe = 'klarnapaylater_de.png';
    protected $isServerToServer = true;
    protected $isLogoDe = true;
}
