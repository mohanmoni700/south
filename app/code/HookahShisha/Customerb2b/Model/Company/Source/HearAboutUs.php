<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class HearAboutUs Config
 *
 */
class HearAboutUs implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        // $options = [
        //     ['label' => __('Returning Customer'), 'value' => 'returning_customer'],
        //     ['label' => __('Headquest'), 'value' => 'headquest'],
        //     ['label' => __('B.I.G.'), 'value' => 'big'],
        //     ['label' => __('RTDA'), 'value' => 'rtda'],
        //     ['label' => __('C.H.A.M.P.S. Show'), 'value' => 'champs_show'],
        //     ['label' => __('Google'), 'value' => 'google'],
        //     ['label' => __('Yahoo!'), 'value' => 'yahoo'],
        //     ['label' => __('Other Search Engine'), 'value' => 'other_search_engine'],
        //     ['label' => __('Flyer'), 'value' => 'flyer'],
        //     ['label' => __('Friend/Family member'), 'value' => 'friend_family_member'],
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
            'returning_customer' => 'Returning Customer',
            'headquest' => 'Headquest',
            'big' => 'B.I.G.',
            'rtda' => 'RTDA',
            'champs_show' => 'C.H.A.M.P.S. Show',
            'google' => 'Google',
            'yahoo' => 'Yahoo!',
            'other_search_engine' => 'Other Search Engine',
            'flyer' => 'Flyer',
            'friend_family_member' => 'Friend/Family member',
        ];
        return $optionArray;
    }
}
