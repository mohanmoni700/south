<?php

namespace HookahShisha\Customization\Model\Sales\Pdf;

class SubtotalDisc extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
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
        $subDisc = $this->getOrder()->getTotalSubtotalDiscount();
        $subInclTax = $this->getOrder()->formatPriceTxt($subDisc);
        $value = $subDisc == 0 ? '' : '-' . $subInclTax;

        $total = [
            'amount' => $value,
            'label' => 'Subtotal Discount',
            'font_size' => $fontSize,
        ];

        return [$total];
    }
}
