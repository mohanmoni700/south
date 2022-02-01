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
 * Class MapMarkerColor
 *
 * @package MageModule\Core\Model\Entity\Attribute\Source
 */
class MapMarkerColor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    private $colors = [
        'black'     => 'Black',
        'blue'      => 'Blue',
        'gray'      => 'Gray',
        'green'     => 'Green',
        'orange'    => 'Orange',
        'purple'    => 'Purple',
        'red'       => 'Red',
        'turquoise' => 'Turquoise',
        'yellow'    => 'Yellow'
    ];

    /**
     * MapMarkerColor constructor.
     *
     * @param array $colors
     */
    public function __construct(
        $colors = []
    ) {
        $this->colors = array_merge($this->colors, $colors);
        asort($this->colors);
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];

            foreach ($this->getOptionArray() as $value => $label) {
                $this->_options[] = ['value' => $value, 'label' => __($label)];
            }
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return $this->colors;
    }
}
