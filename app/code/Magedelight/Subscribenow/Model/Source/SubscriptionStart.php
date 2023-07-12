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

class SubscriptionStart extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * Option values.
     */
    const MOMENT = 'moment_of_purchase';
    const DEFINE_BY_CUSTOMER = 'defined_by_customer';
    const LAST_DAY_MONTH = 'last_day_month';
    const EXACT_DAY = 'exact_day_month';

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Moment Of Purchase'), 'value' => self::MOMENT],
                ['label' => __('Defined By Customer'), 'value' => self::DEFINE_BY_CUSTOMER],
                ['label' => __('Last Day Of Month'), 'value' => self::LAST_DAY_MONTH],
                ['label' => __('Exact Day Of Month'), 'value' => self::EXACT_DAY],
            ];
        }

        return $this->_options;
    }

    public function getIndexOptionText($value)
    {
        switch ($value) {
            case self::MOMENT:
                return 'Moment Of Purchase';
            case self::DEFINE_BY_CUSTOMER:
                return 'Defined By Customer';
            case self::LAST_DAY_MONTH:
                return 'Last Day Of Month';
            case self::EXACT_DAY:
                return 'Exact Day Of Month';
        }

        return parent::getIndexOptionText($value);
    }
    
    /**
     * set attribute value if catalog_product_flat enable.
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }
}
