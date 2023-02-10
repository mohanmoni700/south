<?php

namespace Infibeam\Ccavenue\Model\Source;

class CurrencyList implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        return [
            [
                'value' => "INR",
                'label' => __('Indian Rupee')
            ],
            [
                'value' => "USD",
                'label' => __('US Dollar')
            ],
            [
                'value' => "AED",
                'label' => __('United Arab Emirates Dirham')
            ],
            [
                'value' => "SGD",
                'label' => __('Singapore Dollar')
            ],
            [
                'value' => "GBP",
                'label' => __('Pound Sterling')
            ],
            [
                'value' => "EUR",
                'label' => __('Euro')
            ],
            [
                'value' => "AUD",
                'label' => __('Australian Dollar')
            ],
            [
                'value' => "CAD",
                'label' => __('Canadian Dollar')
            ],
            [
                'value' => "HKD",
                'label' => __('Hong Kong Dollar')
            ],
            [
                'value' => "JPY",
                'label' => __('Japanese Yen')
            ],
            [
                'value' => "NZD",
                'label' => __('New Zealand Dollar')
            ],
            [
                'value' => "SAR",
                'label' => __('Saudi Riyal')
            ],
        ];
    }

}
