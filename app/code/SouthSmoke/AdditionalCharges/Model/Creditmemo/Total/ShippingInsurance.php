<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class ShippingInsurance extends AbstractTotal
{
    /**
     * Collect totals process.
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo): ShippingInsurance
    {
        if ($amount = $creditmemo->getOrder()->getShippingInsurance()) {
            $creditmemo->setShippingInsurance($amount);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getShippingInsurance() ?? 0);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getShippingInsurance() ?? 0);
        }
        return $this;
    }
}

