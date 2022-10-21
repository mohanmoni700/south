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
        $options = [];
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
        return [
            'distributor' => 'Distributor',
            'retail' => 'Retail',
            'hookah_lounge' => 'Hookah Lounge',
            'catering' => 'Catering',
            'bar_night_club' => 'Bar/Night Club',
        ];
    }
}
