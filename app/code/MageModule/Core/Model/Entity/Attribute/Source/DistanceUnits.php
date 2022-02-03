<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\Entity\Attribute\Source;

/**
 * Class DistanceUnits
 *
 * @package MageModule\Core\Model\Entity\Attribute\Source
 */
class DistanceUnits extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const UNIT_KILOMETERS = 'kilometers';
    const UNIT_MILES      = 'miles';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Kilometers'), 'value' => self::UNIT_KILOMETERS],
                ['label' => __('Miles'), 'value' => self::UNIT_MILES],
            ];
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}
