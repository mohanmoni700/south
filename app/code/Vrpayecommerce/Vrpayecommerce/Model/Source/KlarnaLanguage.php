<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Klarna Language Dropdown source
 */
class KlarnaLanguage implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '27',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_DANISH')
            ],
            [
                'value' => '28',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_AUSTRIA')
            ],
            [
                'value' => '28',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_GERMAN')
            ],
            [
                'value' => '37',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_FINNISH')
            ],
            [
                'value' => '97',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_NORWEGIAN')
            ],
            [
                'value' => '101',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_DUTCH')
            ],
            [
                'value' => '138',
                'label' => __('BACKEND_CH_KLARNA_LANGUAGE_SWEDISH')
            ]
        ];
    }
}
