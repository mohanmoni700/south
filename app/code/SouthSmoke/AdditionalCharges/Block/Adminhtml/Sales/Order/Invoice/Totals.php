<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Block\Adminhtml\Sales\Order\Invoice;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class Totals extends Template
{
    /**
     * Show insurances in invoice pages
     *
     * @return $this
     */
    public function initTotals(): Totals
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source->getShippingInsurance()) {
            $shippingInsurance = new DataObject(
                [
                    'code' => 'shipping_insurance',
                    'value' => $source->getShippingInsurance(),
                    'label' => __("Shipping Insurance")
                ]
            );
            $parent->addTotal($shippingInsurance, 'shipping_insurance', 'grand_total');
        }

        if ($source->getComplianceInsurance()) {
            $complianceInsurance = new DataObject(
                [
                    'code' => 'compliance_insurance',
                    'value' => $source->getComplianceInsurance(),
                    'label' => __("Compliance Insurance")
                ]
            );
            $parent->addTotal($complianceInsurance, 'compliance_insurance', 'shipping_insurance');
        }

        return $this;
    }

}
