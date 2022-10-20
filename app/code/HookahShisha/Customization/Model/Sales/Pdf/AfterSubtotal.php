<?php
namespace HookahShisha\Customization\Model\Sales\Pdf;

class AfterSubtotal
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
     * @param \Magento\Tax\Model\Sales\Pdf\Subtotal $subject
     * @param array $result
     * @return array
     */
    public function afterGetTotalsForDisplay(\Magento\Tax\Model\Sales\Pdf\Subtotal $subject, $result)
    {
        $store = $subject->getOrder()->getStore();

        $amountInclTaxConfig = $this->_taxConfig->displaySalesSubtotalInclTax($store);

        $discountAmount = $subject->getOrder()->getTotalSubtotalDiscount();

        if ($subject->getSource()->getSubtotalInclTax()) {
            $amountInclTax = $subject->getSource()->getSubtotalInclTax();
        } else {
            $amountInclTax = $subject->getAmount() +
            $subject->getSource()->getTaxAmount() -
            $subject->getSource()->getShippingTaxAmount();
        }

        $newamount = $subject->getAmount();

        if ($subject->getAmount() > 0) {
            $newamount = $amountInclTaxConfig ? $amountInclTax : $subject->getAmount() + $discountAmount;
        }

        $amount = $subject->getOrder()->formatPriceTxt($newamount);

        foreach ($result as $key => $value) {
            $result[$key]['amount'] = $subject->getAmountPrefix() . $amount;
        }
        return $result;
    }
}
