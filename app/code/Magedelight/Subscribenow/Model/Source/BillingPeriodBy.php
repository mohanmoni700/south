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

class BillingPeriodBy extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * Option values.
     */
    const ADMIN = 'admin';
    const CUSTOMER = 'customer';

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Admin'), 'value' => self::ADMIN],
                ['label' => __('Customer'), 'value' => self::CUSTOMER],
            ];
        }

        return $this->_options;
    }

    public function getIndexOptionText($value)
    {
        switch ($value) {
            case self::ADMIN:
                return 'Admin';
            case self::CUSTOMER:
                return 'Customer';
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
