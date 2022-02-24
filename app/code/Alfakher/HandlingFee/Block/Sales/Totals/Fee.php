<?php

namespace Alfakher\HandlingFee\Block\Sales\Totals;

/**
 * Display handling fee in customer account
 *
 * @author af_bv_op
 */
class Fee extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get order's store id
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Return order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Lable property
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Value property
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Calculate totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        // $store = $this->getStore();

        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'handling_fee',
                'strong' => false,
                'value' => $this->_source->getHandlingFee(),
                'label' => "Handling Fee",
            ]
        );

        $parent->addTotal($fee, 'handling_fee');

        return $this;
    }
}
