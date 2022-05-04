<?php

namespace Alfakher\HandlingFee\Block\Adminhtml\Sales\Order\Invoice;

/**
 * Appending Handling fee in invoice
 *
 * @author af_bv_op
 */
class Fee extends \Magento\Framework\View\Element\Template
{

    /**
     * @var $_dataHelper
     */
    protected $_dataHelper;

    /**
     * @var $_invoice
     */
    protected $_invoice = null;

    /**
     * @var $_source
     */
    protected $_source;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    /*
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    array $data = []
    ) {
    parent::__construct($context, $data);
    }
     */

    /**
     * Get source object
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * Calaculate totals
     */
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
                'label' => "Shipping Adjustment",
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
