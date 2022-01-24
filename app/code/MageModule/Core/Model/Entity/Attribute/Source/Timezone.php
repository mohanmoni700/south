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

use Magento\Config\Model\Config\Source\Locale\Timezone as ConfigSource;

/**
 * Class Timezone
 *
 * @package MageModule\Core\Model\Entity\Attribute\Source
 */
class Timezone extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var ConfigSource
     */
    private $source;

    /**
     * Timezone constructor.
     *
     * @param ConfigSource $source
     */
    public function __construct(
        ConfigSource $source
    ) {
        $this->source = $source;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];

            foreach ($this->source->toOptionArray() as $option) {
                $this->_options[] = $option;
            }
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
