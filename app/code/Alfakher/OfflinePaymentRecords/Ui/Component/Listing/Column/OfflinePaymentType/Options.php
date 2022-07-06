<?php

namespace Alfakher\OfflinePaymentRecords\Ui\Component\Listing\Column\OfflinePaymentType;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "Net 15", 'label' => __('Net 15')],
            ['value' => "Net 30", 'label' => __('Net 30')],
            ['value' => "Paypal", 'label' => __('Paypal')],
            ['value' => "Wire Transfer", 'label' => __('Wire Transfer')],
            ['value' => "ACH", 'label' => __('ACH')],
        ];
    }
}
