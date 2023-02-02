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

namespace Magedelight\Subscribenow\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_invoice = null;
    
    /**
     * @var \Magedelight\Subscribenow\Block\Sales\Order\ExtensionAttributes
     */
    private $extensionAttribute;
    
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Subscribenow\Block\Sales\Order\ExtensionAttributes $extensionAttribute,
        array $data = []
    ) {
        $this->extensionAttribute = $extensionAttribute;
        parent::__construct($context, $data);
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
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
