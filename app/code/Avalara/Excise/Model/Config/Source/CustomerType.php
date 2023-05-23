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

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Customer type config values
 */
class CustomerType extends AbstractSource
{   
    /**
     * @return array
     */
    public function getAllOptions()
    {
       return [
                ['label'=>__('Direct'), 'value'=>'DIRECT'],
                ['label'=>__('Wholesale'), 'value'=>'WHOLESALE'],
                ['label'=>__('Retail'), 'value'=>'RETAIL'],
            ];
    }
}
