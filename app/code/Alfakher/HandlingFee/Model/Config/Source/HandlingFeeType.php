<?php

namespace Alfakher\HandlingFee\Model\Config\Source;

/**
 * Preparing option list for store config
 *
 * @author af_bv_op
 */
class HandlingFeeType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Option list
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'percentage', 'label' => __('Percentage')],
            ['value' => 'fixed', 'label' => __('Fixed')],
        ];
    }
}
