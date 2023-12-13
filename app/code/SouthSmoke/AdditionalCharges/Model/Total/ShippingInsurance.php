<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Model\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use SouthSmoke\AdditionalCharges\Helper\Config;

class ShippingInsurance extends AbstractTotal
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
        if (count($shippingAssignment->getItems())
            && $this->configHelper->getShippingInsurance(Config::STATUS)) {
            $amount = $this->configHelper->getShippingInsurance(Config::AMOUNT);

            $subTotal = $total->getSubtotal();
            if ($subTotal > ($this->configHelper->getShippingInsurance(Config::MINIMUM_ORDER_AMOUNT) ?? INF)) {
                $amount = $subTotal * ($this->configHelper->getShippingInsurance(Config::PERCENT)/100);
            }

            $total->setTotalAmount('shipping_insurance', $amount);
            $total->setBaseTotalAmount('shipping_insurance', $amount);

            $total->setShippingInsurance($amount);
            $total->setBaseShippingInsurance($amount);
            $quote->setShippingInsurance($amount);
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
            'code' => 'shipping_insurance',
            'title' => __('Shipping insurance'),
            'value' => $total->getShippingInsurance()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase|mixed
     */
    public function getLabel()
    {
        return __('Shipping insurance');
    }
}
