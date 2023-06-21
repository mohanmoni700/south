<?php

namespace SouthSmoke\AdditionalCharges\Block\Sales\Email;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class Totals extends Template
{
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source->getShippingInsurance()) {
            $shippingInsurance = new DataObject(
                [
                    'code' => 'shipping_insurance',
                    'strong' => false,
                    'value' => $source->getShippingInsurance(),
                    'label' => __("Shipping Insurance"),
                ]
            );
            $parent->addTotalBefore($shippingInsurance, 'tax');
        }
        if ($source->getComplianceInsurance()) {
            $complianceInsurance = new DataObject(
                [
                    'code' => 'compliance_insurance',
                    'strong' => false,
                    'value' => $source->getComplianceInsurance(),
                    'label' => __("Compliance Insurance") ,
                ]
            );
            $parent->addTotalBefore($complianceInsurance, 'shipping_insurance');
        }
        return $this;
    }
}
