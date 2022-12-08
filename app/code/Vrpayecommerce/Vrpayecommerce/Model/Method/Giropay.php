<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class Giropay extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_giropay';
    protected $brand = 'GIROPAY';
    protected $methodTitle = 'FRONTEND_PM_GIROPAY';
    protected $logo = 'giropay.png';
    protected $testMode = 'INTERNAL';
}
