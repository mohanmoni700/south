<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\Config;

class IntervalType extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Sets name for input element.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $intervaTypes = [
                0 => ['value' => 'day', 'label' => __('Day')],
                1 => ['value' => 'week', 'label' => __('Week')],
                2 => ['value' => 'month', 'label' => __('Month')],
                3 => ['value' => 'year', 'label' => __('Year')],
            ];
            foreach ($intervaTypes as $intervaType) {
                if (isset($intervaType['value']) && $intervaType['value'] && isset($intervaType['label']) && $intervaType['label']) {
                    $this->addOption($intervaType['value'], $intervaType['label']);
                }
            }
        }

        return parent::_toHtml();
    }
}
