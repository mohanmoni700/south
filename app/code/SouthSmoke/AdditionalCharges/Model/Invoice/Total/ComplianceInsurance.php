<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class ComplianceInsurance extends AbstractTotal
{
    /**
     * Collect totals process.
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        if ($amount = $invoice->getOrder()->getComplianceInsurance()) {
            $invoice->setComplianceInsurance($amount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getComplianceInsurance());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getComplianceInsurance());
        }
        return $this;
    }
}
