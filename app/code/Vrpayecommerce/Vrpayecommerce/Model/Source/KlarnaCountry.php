<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Klarna Country Dropdown source
 */
class KlarnaCountry implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '15',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_AUSTRIA')
            ],
            [
                'value' => '59',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_DENMARK')
            ],
            [
                'value' => '73',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_FINLAND')
            ],
            [
                'value' => '81',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_GERMANY')
            ],
            [
                'value' => '154',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_NETHERLANDS')
            ],
            [
                'value' => '164',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_NORWAY')
            ],
            [
                'value' => '209',
                'label' => __('BACKEND_CH_KLARNA_COUNTRY_SWEDEN')
            ]
        ];
    }
}
