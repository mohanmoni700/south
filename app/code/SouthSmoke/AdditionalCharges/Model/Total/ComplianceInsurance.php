<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use SouthSmoke\AdditionalCharges\Helper\Config;

class ComplianceInsurance extends AbstractTotal
{
    /**
     * @var Config
     */
    private Config $configHelper;

    /**
     * @param Config $configHelper
     */
    public function __construct(
        Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Collect totals process.
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|ComplianceInsurance
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (count($shippingAssignment->getItems()) && $this->configHelper->getComplianceInsurance(Config::STATUS)) {
            $amount = $this->configHelper->getComplianceInsurance(Config::AMOUNT) ?? 0.99;
            $total->setTotalAmount('compliance_insurance', $amount);
            $total->setBaseTotalAmount('compliance_insurance', $amount);

            $total->setComplianceInsurance($amount);
            $total->setComplianceInsurance($amount);
            $quote->setComplianceInsurance($amount);
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        return [
            'code' => 'compliance_insurance',
            'title' => __('Compliance insurance'),
            'value' => $total->getComplianceInsurance()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase|mixed
     */
    public function getLabel()
    {
        return __('Compliance insurance');
    }
}
