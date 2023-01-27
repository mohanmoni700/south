<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\Form;

use Alfakher\SlopePayment\Block\Form\AbstractInstruction;

/**
 * Block for Slope payment method form
 */
class SlopePayment extends AbstractInstruction
{
    /**
     * Slope payment template
     *
     * @var string
     */
    protected $_template = 'Alfakher_SlopePayment::form/slopepayment.phtml';
}
