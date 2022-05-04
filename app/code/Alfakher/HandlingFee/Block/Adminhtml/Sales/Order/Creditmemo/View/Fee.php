<?php

namespace Alfakher\HandlingFee\Block\Adminhtml\Sales\Order\Creditmemo\View;

/**
 * Displaying handling fee in credit memi
 *
 * @author af_bv_op
 */
class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * OrderFee constructor.
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
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Fetch credit memo
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();

        if ($this->getSource()->getHandlingFee() <= 0) {
            return $this;
        }
        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'handling_fee',
                'strong' => false,
                'value' => $this->getSource()->getHandlingFee(),
                'label' => "Shipping Adjustment",
            ]
        );

        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}
