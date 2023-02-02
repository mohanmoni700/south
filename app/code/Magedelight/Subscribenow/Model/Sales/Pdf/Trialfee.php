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

namespace Magedelight\Subscribenow\Model\Sales\Pdf;

class Trialfee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    /**
     * @param \Magento\Tax\Helper\Data                                           $taxHelper
     * @param \Magento\Tax\Model\Calculation                                     $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param array                                                              $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $trialAmount = $this->getOrder()->getSubscribenowTrialAmount();
        $hasTrial = $this->getOrder()->getHasTrial();
        if (empty($trialAmount) || $trialAmount == 0) {
            if (!$hasTrial) {
                return [];
            }
        }
        $amount = $this->getOrder()->formatPriceTxt($trialAmount);

        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];

        return [$total];
    }
}
