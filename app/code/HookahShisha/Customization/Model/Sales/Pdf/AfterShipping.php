<?php
namespace HookahShisha\Customization\Model\Sales\Pdf;

class AfterShipping
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->_taxConfig = $taxConfig;
    }

    /**
     * Get array of arrays with totals information for display in PDF
     *
     * @param \Magento\Tax\Model\Sales\Pdf\Shipping $subject
     * @param array $result
     * @return array
     */
    public function afterGetTotalsForDisplay(\Magento\Tax\Model\Sales\Pdf\Shipping $subject, $result)
    {
        $handelingFee = $subject->getOrder()->getHandlingFee();

        $totalShippingFeeDiscount = $subject->getOrder()->getTotalShippingFeeDiscount();

        $store = $subject->getOrder()->getStore();

        $amountInclTaxConfig = $this->_taxConfig->displaySalesShippingInclTax($store);

        $amountInclTax = $subject->getSource()->getShippingInclTax();
        if (!$amountInclTax) {
            $amountInclTax = $subject->getAmount() + $subject->getSource()->getShippingTaxAmount();
        }
        $newamount = $amountInclTaxConfig ? $amountInclTax + $handelingFee + $totalShippingFeeDiscount
        : $subject->getAmount() + $handelingFee + $totalShippingFeeDiscount;

        $amount = $subject->getOrder()->formatPriceTxt($newamount);
        foreach ($result as $key => $value) {
            $result[$key]['amount'] = $subject->getAmountPrefix() . $amount;
        }
        return $result;
    }
}
