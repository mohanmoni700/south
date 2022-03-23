<?php
/**
 * A Magento 2 module named Avalara/Excise
 * Copyright (C) 2019
 *
 * This file included in Avalara/Excise is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Avalara\Excise\Model\Config\Source;

class TaxMode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'Estimate Excise tax',
            'label' => __('Estimate Excise tax')],
            ['value' => 'Estimate tax and Submit transaction to AvaTax',
            'label' => __('Estimate tax and Submit transaction to AvaTax')]
        ];
    }

    public function toArray()
    {
        return ['Estimate Excise tax' => __('Estimate Excise tax'),
        'Estimate tax and Submit transaction to AvaTax' => __('Estimate tax and Submit transaction to AvaTax')];
    }
}
