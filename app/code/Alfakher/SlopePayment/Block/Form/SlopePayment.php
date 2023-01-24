<?php
declare (strict_types = 1);

namespace Alfakher\SlopePayment\Block\Form;

/**
 * Block for Slope payment method form
 */
class SlopePayment extends \Alfakher\SlopePayment\Block\Form\AbstractInstruction
{
    /**
     * Slope payment template
     *
     * @var string
     */
    protected $_template = 'Alfakher_SlopePayment::form/slopepayment.phtml';
}
