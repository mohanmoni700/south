<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Card Type Dropdown source
 */
class CardType implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'VISA',
                'label' => __('BACKEND_CC_VISA')
            ],
            [
                'value' => 'MASTER',
                'label' => __('BACKEND_CC_MASTER')
            ],
            [
                'value' => 'AMEX',
                'label' => __('BACKEND_CC_AMEX')
            ],
            [
                'value' => 'DINERS',
                'label' => __('BACKEND_CC_DINERS')
            ],
            [
                'value' => 'JCB',
                'label' => __('BACKEND_CC_JCB')
            ]

        ];
    }
}
