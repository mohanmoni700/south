<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Quote\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magedelight\Subscribenow\Helper\Data as DataHelper;
use Magedelight\Subscribenow\Model\Service\CalculationService;
use Magedelight\Subscribenow\Model\Subscription;

/**
 * Class InitAmount
 * @package Magedelight\Subscribenow\Model\Quote\Total
 */
class InitAmount extends Address\Total\AbstractTotal
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @param DataHelper $helper
     * @param CalculationService $calculator
     */
    public function __construct(DataHelper $helper, CalculationService $calculator)
    {
        $this->calculator = $calculator;
        $this->helper = $helper;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->helper->isModuleEnable() || !count($shippingAssignment->getItems())) {
            return $this;
        }

        $fees = $this->calculator->calculate($quote, 'init_amount');
        if (!$fees) {
            return $this;
        } elseif ($fees && !$fees->getAmount()) {
            return $this;
        }
        
        $total->setTotalAmount(Subscription::INIT_AMOUNT_FIELD_NAME, $fees->getAmount());
        $total->setBaseTotalAmount(Subscription::INIT_AMOUNT_FIELD_NAME, $fees->getBaseAmount());
        $total->setSubscribenowInitAmount($fees->getAmount());
        $total->setBaseSubscribenowInitAmount($fees->getBaseAmount());
        
        $quote->setGrandTotal($total->getGrandTotal() + $fees->getAmount());
        $quote->setBaseGrandTotal($total->getBaseGrandTotal() + $fees->getBaseAmount());
        $quote->setSubscribenowInitAmount($fees->getAmount());
        $quote->setBaseSubscribenowInitAmount($fees->getBaseAmount());
        
        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Address\Total $total)
    {
        $result = [];
        
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }
        
        $fees = $this->calculator->calculate($quote, 'init_amount');

        if ($fees->getAmount() > 0.0) {
            $result = [
                'code' => Subscription::INIT_AMOUNT_FIELD_NAME,
                'title' => $this->getLabel(),
                'value' => $fees->getAmount()
            ];
        }

        return $result;
    }

    /**
     * Get label
     *
     * @return Phrase
     */
    public function getLabel()
    {
        return $this->helper->getInitAmountTitle();
    }
}
