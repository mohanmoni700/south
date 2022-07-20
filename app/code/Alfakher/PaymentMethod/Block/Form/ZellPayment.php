<?php
namespace Alfakher\PaymentMethod\Block\Form;

/**
 * Block for Zelle payment method form
 */
class ZellePayment extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Zelle template
     *
     * @var string
     */
    protected $_template = 'Alfakher_PaymentMethod::form/zellepayment.phtml';
}
