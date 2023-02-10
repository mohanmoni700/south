<?php

namespace Infibeam\Ccavenue\Model\Source;

class IntegrationType implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        return [
            [
                'value' => "iframe",
                'label' => __('IFrame')
            ],
            [
                'value' => "redirect",
                'label' => __('Redirect')
            ]
        ];
    }

}

