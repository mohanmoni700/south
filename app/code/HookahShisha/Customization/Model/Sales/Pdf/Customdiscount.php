<?php

namespace HookahShisha\Customization\Model\Sales\Pdf;

class Customdiscount extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
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
        $SubDiscount = $this->getOrder()->getTotalSubtotalDiscount();
        if ($SubDiscount === null) {
            return [];
        }
        $subInclTax = $this->getOrder()->formatPriceTxt($SubDiscount);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = [
            'amount' => '-'.$this->getAmountPrefix() . $subInclTax,
            'label' => 'Subtotal Discount',
            'font_size' => $fontSize
        ];

        $ShippingDisc= $this->getOrder()->getTotalShippingFeeDiscount();
        if ($ShippingDisc === null) {
            return [];
        }
        $shippingInclTax = $this->getOrder()->formatPriceTxt($ShippingDisc);
        $total = [
            'amount' => '-'.$this->getAmountPrefix() . $shippingInclTax,
            'label' => 'Shipping Fee Discount',
            'font_size' => $fontSize
        ];

        return [$total];
    }
}
