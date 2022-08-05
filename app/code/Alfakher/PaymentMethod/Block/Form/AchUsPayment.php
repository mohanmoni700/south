<?php
namespace Alfakher\PaymentMethod\Block\Form;

class AchUsPayment extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Alfakher_PaymentMethod::form/ach_us_payment.phtml';
}
