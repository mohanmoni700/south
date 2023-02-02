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

namespace Magedelight\Subscribenow\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo = null;

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

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
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
