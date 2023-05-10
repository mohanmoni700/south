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

namespace Magedelight\Subscribenow\Block\Adminhtml\Sales;

/**
 * Class Totals
 * @package Magedelight\Subscribenow\Block\Adminhtml\Sales
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;
    
    /**
     * @var \Magedelight\Subscribenow\Block\Sales\Order\ExtensionAttributes
     */
    private $extensionAttribute;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magedelight\Subscribenow\Block\Sales\Order\ExtensionAttributes $extensionAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        \Magedelight\Subscribenow\Block\Sales\Order\ExtensionAttributes $extensionAttribute,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
        $this->extensionAttribute = $extensionAttribute;
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $dataSource = $this->getSource();

        $initAmount = $this->extensionAttribute->addInitAmount($dataSource);
        if ($initAmount) {
            $this->getParentBlock()->addTotalBefore($initAmount, 'grand_total');
        }
        
        $trialAmount = $this->extensionAttribute->addTrialAmount($dataSource);
        if ($trialAmount) {
            $this->getParentBlock()->addTotalBefore($trialAmount, 'grand_total');
        }

        return $this;
    }
}
