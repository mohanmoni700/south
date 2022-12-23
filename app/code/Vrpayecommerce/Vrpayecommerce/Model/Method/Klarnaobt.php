<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class Klarnaobt extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_klarnaobt';
    protected $brand = 'SOFORTUEBERWEISUNG';
    protected $methodTitle = 'FRONTEND_PM_KLARNAOBT';
    protected $logo = 'klarnaonlinebanktransfer_en.png';
    protected $logoDe = 'klarnaonlinebanktransfer_de.png';
    protected $isLogoDe = true;
}