<?php

namespace Alfakher\DocumentStatus\Model\Source;

class DocumentStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Option Array
     *
     * @return array toOptionArray
     */
    public function toOptionArray()
    {
        return [
            ['value' => "approve", 'label' => __('Approved')],
            ['value' => "rejected", 'label' => __('Rejected')],
            ['value' => "pending", 'label' => __('Pending')],
            ['value' => "expire", 'label' => __('Expired')],
        ];
    }
}
