<?php

namespace Vrpayecommerce\Vrpayecommerce\Model\Method;


class Enterpay extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_enterpay';
    protected $brand = 'ENTERPAY';
    protected $methodTitle = 'FRONTEND_PM_ENTERPAY';
    protected $paymentType = 'PA';
    protected $logo = 'enterpay.png';
    protected $isServerToServer = true;
}
