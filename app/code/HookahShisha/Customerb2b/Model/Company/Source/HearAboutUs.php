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
    }
}
