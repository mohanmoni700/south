<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Server Mode Dropdown source
 */
class ServerMode implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'TEST',
                'label' => __('BACKEND_CH_MODE_TEST')
            ],
            [
                'value' => 'LIVE',
                'label' => __('BACKEND_CH_MODE_LIVE')
            ]
        ];
    }
}
