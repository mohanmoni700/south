<?php

namespace Alfakher\HandlingFee\Model\Invoice\Total;

/**
 *
 */
class HandlingFee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

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
