<?php

namespace Alfakher\HandlingFee\Model\Quote\Total;

/**
 * Display handling fee on cart and checkout page
 *
 * @author af_bv_op
 */
class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     *
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Alfakher\HandlingFee\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Alfakher\HandlingFee\Helper\Data $helperData
    ) {
        $this->quoteValidator = $quoteValidator;
        $this->productMetadata = $productMetadata;
        $this->helperData = $helperData;
    }

    /**
     * Collect totals
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $enabled = $this->helperData->isModuleEnabled();
        // $balance = $this->helperData->getHandlingFee();
        $balance = $this->helperData->getHandlingFee($total->getSubtotal());

        if ($enabled) {
            $total->setTotalAmount('handling_fee', $balance);
            $total->setBaseTotalAmount('handling_fee', $balance);
            $total->setHandlingFee($balance);
            $total->setBaseHandlingFee($balance);
            $quote->setHandlingFee($balance);
            $total->setGrandTotal($total->getGrandTotal() + $balance);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $balance);
        }

        return $this;
    }

    /**
     * Clear values
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * Fetch handling fee
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {

        $enabled = $this->helperData->isModuleEnabled();
        // $fee = $this->helperData->getHandlingFee();
        $fee = $this->helperData->getHandlingFee($total->getSubtotal());
        if ($enabled && $fee) {
            return [
                'code' => 'handling_fee',
                'title' => 'Handling Fee',
                'value' => $fee,
            ];
        } else {
            return [];
        }
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Handling Fee');
    }
}
