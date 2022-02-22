<?php

namespace Alfakher\HandlingFee\Block\Adminhtml\Sales\Order\Invoice;

/**
 *
 */
class HandlingFee extends \Magento\Framework\View\Element\Template
{

    protected $_dataHelper;
    protected $_invoice = null;
    protected $_source;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
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
        $this->getSource();

        if (!$this->getSource()->getOrder()->getHandlingFee()) {
            return $this;
        }

        $orderHandlingFee = $this->getSource()->getOrder()->getHandlingFee();
        $orderHandlingFeeInvoiced = $this->getSource()->getOrder()->getHandlingFeeInvoiced();
        $amount = $orderHandlingFee - $orderHandlingFeeInvoiced;
        if ($amount <= 0) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'handling_fee',
                'value' => $amount,
                'label' => "Handling Fee",
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
