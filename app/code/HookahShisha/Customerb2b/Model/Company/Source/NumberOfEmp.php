<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;

/**
 * Class NumberOfEmp Config
 */
class NumberOfEmp implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        // $options = [
        //     ['label' => __('01-05'), 'value' => '1'],
        //     ['label' => __('06-10'), 'value' => '2'],
        //     ['label' => __('11-15'), 'value' => '3'],
        //     ['label' => __('16-20'), 'value' => '4'],
        //     ['label' => __('21-50'), 'value' => '5'],
        //     ['label' => __('51 and above'), 'value' => '6'],
        // ];

        foreach ($this->getOptionArray() as $key => $value) {
            $options[] = ['label' => __($value), 'value' => $key];
        }

        return $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = [
            1 => '01-05',
            2 => '06-10',
            3 => '11-15',
            4 => '16-20',
            5 => '21-50',
            6 => '51 and above',
        ];
        return $optionArray;
    }
}
