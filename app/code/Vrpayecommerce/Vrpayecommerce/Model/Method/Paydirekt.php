<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class Paydirekt extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_paydirekt';
    protected $brand = 'PAYDIREKT';
    protected $methodTitle = 'FRONTEND_PM_PAYDIREKT';
    protected $logo = 'paydirekt.png';

    /**
     * get a payment type
     * @return string
     */
    public function getPaymentType()
    {
        return $this->getPaymentTypeSelection();
    }
}
