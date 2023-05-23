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

class AvataxCompany implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => null, 'label' => __('No available companies')]];
    }
}
