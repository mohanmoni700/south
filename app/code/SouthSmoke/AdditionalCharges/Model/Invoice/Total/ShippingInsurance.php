<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class ShippingInsurance extends AbstractTotal
{

    /**
     * Collect totals process.
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        if ($amount = $invoice->getOrder()->getShippingInsurance()) {
            $invoice->setShippingInsurance($amount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getShippingInsurance());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getShippingInsurance());
        }
        return $this;
    }
}

