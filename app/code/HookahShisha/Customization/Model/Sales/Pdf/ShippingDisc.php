<?php

namespace HookahShisha\Customization\Model\Sales\Pdf;

class ShippingDisc extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay(): array
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $shippingDisc = $this->getOrder()->getTotalShippingFeeDiscount();
        $shippingInclTax = $this->getOrder()->formatPriceTxt($shippingDisc);
        $value = $shippingDisc == 0 ? '' : '-' . $shippingInclTax;

        $total = [
            'amount' => $value,
            'label' => 'Shipping Fee Discount',
            'font_size' => $fontSize,
        ];

        return [$total];
    }
}
