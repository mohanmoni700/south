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

class LoggingMode implements \Magento\Framework\Option\ArrayInterface
{
    const LOGGING_MODE_DB = 1;
    const LOGGING_MODE_FILE = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' => self::LOGGING_MODE_DB,
                'label' => __('Database')
            ],
            [
                'value' => self::LOGGING_MODE_FILE,
                'label' => __('File')
            ]
        ];
    }

    public function toArray()
    {
        return [self::LOGGING_MODE_DB => __('Database'),self::LOGGING_MODE_FILE => __('File')];
    }
}
