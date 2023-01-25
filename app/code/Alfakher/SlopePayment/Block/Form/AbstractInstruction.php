<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\Form;

use Magento\Payment\Block\Form;

/**
 * Abstract class for slope payment method form
 */
abstract class AbstractInstruction extends Form
{
    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Get instructions text from config
     *
     * @return null|string
     */
    public function getInstructions()
    {
        if ($this->_instructions === null) {
            $method = $this->getMethod();
            $this->_instructions = $method->getConfigData('instructions');
        }
        return $this->_instructions;
    }
}
