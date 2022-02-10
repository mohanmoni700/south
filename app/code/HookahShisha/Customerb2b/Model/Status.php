<?php

namespace HookahShisha\Customerb2b\Model;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Company Account Verified and Detail Changed option
 *
 */
class Status implements OptionSourceInterface
{

    /**
     * Get labels array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            '1' => __('Yes'),
            '0' => __('No'),
        ];
    }

    /**
     * Get labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
