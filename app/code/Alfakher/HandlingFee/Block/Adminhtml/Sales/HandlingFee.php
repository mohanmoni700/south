<?php

namespace Alfakher\HandlingFee\Block\Adminhtml\Sales;

/**
 *
 */
class HandlingFee extends \Magento\Framework\View\Element\Template
{
    protected $_currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currency = $currency;
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
        return $this->_currency->getCurrencySymbol();
    }

    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        if (!$this->getSource()->getHandlingFee()) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'handling_fee',
                'value' => $this->getSource()->getHandlingFee(),
                'label' => "Handling Fee",
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
