<?php

namespace Avalara\Excise\Model\Config\Source;

class AvalaraCountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Creates list of countries to enable for address validation and Tax
     *
     * Currently only the us are supported by Avalara Excise address validation so this is the only one country
     * currently in the option array. More countries should be added to this array when Avalara Excise supports
     * more countries
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'US', 'label' => __('United States')]
        ];

        return $options;
    }
}
