<?php

namespace HookahShisha\Customization\Model\Sales\Pdf;

class NewDiscount extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
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
        $orderDisc= $this->getOrder()->getDiscountAmount();
        $orderValue = $this->getOrder()->formatPriceTxt($orderDisc);
        $value = $this->getOrder()->getGrandTotal() > 0 ? $orderValue : 0 ;
        $total = [
            'amount' => $value,
            'label' => 'Discount',
            'font_size' => $fontSize
        ];

        return [$total];
    }
}
