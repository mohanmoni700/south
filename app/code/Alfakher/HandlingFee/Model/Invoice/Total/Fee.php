<?php

namespace Alfakher\HandlingFee\Model\Invoice\Total;

/**
 * Appending handling fee to invoice
 */
class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect totals
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $orderHandlingFee = $invoice->getOrder()->getHandlingFee();
        $orderHandlingFeeInvoiced = $invoice->getOrder()->getHandlingFeeInvoiced();
        $amount = $orderHandlingFee - $orderHandlingFeeInvoiced;

        if ($amount > 0) {
            $invoice->setHandlingFee(0);
            $invoice->setHandlingFee($amount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getHandlingFee());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getHandlingFee());
        }
        return $this;
    }
}
