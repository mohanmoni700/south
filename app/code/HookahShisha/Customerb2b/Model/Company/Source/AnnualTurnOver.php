<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;

/**
 * Class AnnualTurnOver Config
 */
class AnnualTurnOver implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        /*$options = [
        ['label' => __('0-20000'), 'value' => '1'],
        ['label' => __('20001-50000'), 'value' => '2'],
        ['label' => __('>50001'), 'value' => '3'],
        ];*/
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
            1 => '0-20000',
            2 => '20001-50000',
            3 => '>50001',
        ];
        return $optionArray;
    }
}
