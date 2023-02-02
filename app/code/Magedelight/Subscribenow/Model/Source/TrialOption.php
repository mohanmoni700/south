<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Source;

class TrialOption extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * Option values.
     */
    const NO = '0';
    const YES = '1';

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('No'), 'value' => self::NO],
                ['label' => __('Yes'), 'value' => self::YES],
            ];
        }

        return $this->_options;
    }

    public function getIndexOptionText($value)
    {
        switch ($value) {
            case self::YES:
                return 'Yes';
            case self::NO:
                return 'No';
        }

        return parent::getIndexOptionText($value);
    }
}
