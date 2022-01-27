<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;

/**
 * Class Businesstype Config
 */
class Businesstype implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        // $options = [
        //     ['label' => __('Distributor'), 'value' => 'distributor'],
        //     ['label' => __('Retail Approval'), 'value' => 'retail'],
        //     ['label' => __('Hookah Lounge'), 'value' => 'hookah_lounge'],
        //     ['label' => __('Catering'), 'value' => 'catering'],
        //     ['label' => __('Bar/Night Club'), 'value' => 'bar_night_club'],
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
            'distributor' => 'Distributor',
            'retail' => 'Retail Approval',
            'hookah_lounge' => 'Hookah Lounge',
            'catering' => 'Catering',
            'bar_night_club' => 'Bar/Night Club',
        ];
        return $optionArray;
    }
}
