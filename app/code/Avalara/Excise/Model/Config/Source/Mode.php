<?php

namespace Avalara\Excise\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    const DEVELOPMENT = 0;
    const PRODUCTION = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCTION, 'label' => __('Production')],
            ['value' => self::DEVELOPMENT, 'label' => __('Development')]
        ];
    }
}
