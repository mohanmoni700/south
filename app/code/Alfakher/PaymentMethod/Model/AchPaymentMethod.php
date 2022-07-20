<?php
namespace Alfakher\PaymentMethod\Model;

/**
 * Pay In Store payment method model
 */
class AchPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ach_us_payment';
}
