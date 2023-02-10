<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Transaction Mode Dropdown source
 */
class TransactionMode implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'DB',
                'label' => __('BACKEND_CH_MODEDEBIT')
            ],
            [
                'value' => 'PA',
                'label' => __('BACKEND_CH_MODEPREAUTH')
            ]
        ];
    }
}
