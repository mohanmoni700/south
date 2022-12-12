<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Klarna Currency Dropdown source
 */
class KlarnaCurrency implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('BACKEND_CH_KLARNA_CURRENCY_SWEDISH')
            ],
            [
                'value' => '1',
                'label' => __('BACKEND_CH_KLARNA_CURRENCY_NORWEGIAN')
            ],
            [
                'value' => '2',
                'label' => __('BACKEND_CH_KLARNA_CURRENCY_EURO')
            ],
            [
                'value' => '3',
                'label' => __('BACKEND_CH_KLARNA_CURRENCY_DANISH')
            ]
        ];
    }
}
