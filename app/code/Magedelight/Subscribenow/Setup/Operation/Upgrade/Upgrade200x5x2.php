<?php
/**
 * Magedelight
 * Copyright (C) 2022 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2022 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Setup\Operation\Upgrade;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class Upgrade200x5x2
 * Updated Product Attribute for Better User Instruction On Product Edit Page
 * @package Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class Upgrade200x5x2
{
    const VERSION = '200.5.2';

    private $eavSetup;
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgradeData($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), self::VERSION, '<')) {
            $this->updateAttribute($setup, $sales);
        }
    }

    /**
     * Update Product Attribute
     */
    private function updateAttribute($setup)
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            'discount_amount',
            ['note' => __("Discount will applied on product price.")]
        );

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            'define_start_from',
            ['note' => __("If you set subscription start from is defined by customer, last day of month OR exact day of month, Then you must have to add initial fee for future subscription.")]
        );

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            'initial_amount',
            ['note' => __("Enter Initial Fee, If you've choosen subscription start from: defined by customer, last day of month or exact day of month. cause this will create future subscription and future subscription must need initial fee for process subscription order")]
        );
    }
}
