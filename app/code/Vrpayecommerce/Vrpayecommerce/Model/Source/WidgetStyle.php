<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Vrpayecommerce Widget Style Dropdown source
 */
class WidgetStyle implements ArrayInterface
{
    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'card',
                'label' => __('BACKEND_GENERAL_WIDGETSTYLE_CARD')
            ],
            [
                'value' => 'plain',
                'label' => __('BACKEND_GENERAL_WIDGETSTYLE_PLAIN')
            ],
            [
                'value' => 'custom',
                'label' => __('BACKEND_GENERAL_WIDGETSTYLE_CUSTOM')
            ]
        ];
    }
}
