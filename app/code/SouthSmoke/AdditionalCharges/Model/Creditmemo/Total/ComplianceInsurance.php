<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class ComplianceInsurance extends AbstractTotal
{
    /**
     * Collect totals process.
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        if ($amount = $creditmemo->getOrder()->getComplianceInsurance()) {
            $creditmemo->setComplianceInsurance($amount);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getComplianceInsurance());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getComplianceInsurance());
        }
        return $this;
    }
}
