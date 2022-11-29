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

class DiscountType extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * Option values.
     */
    const PERCENTAGE = 'percentage';
    const FIXED = 'fixed';

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
    ) {
        $this->_eavAttrEntity = $eavAttrEntity;
    }

    /**
     * Retrieve all options array.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Fixed'), 'value' => self::FIXED],
                ['label' => __('Percentage'), 'value' => self::PERCENTAGE],
            ];
        }

        return $this->_options;
    }

    public function getIndexOptionText($value)
    {
        switch ($value) {
            case self::FIXED:
                return 'Fixed';
            case self::PERCENTAGE:
                return 'Percentage';
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
