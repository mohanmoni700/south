<?php

namespace Alfakher\HandlingFee\Model\Config\Source;

/**
 *
 */
class HandlingFeeType implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'percentage', 'label' => __('Percentage')],
            ['value' => 'fixed', 'label' => __('Fixed')],
        ];
    }
}
