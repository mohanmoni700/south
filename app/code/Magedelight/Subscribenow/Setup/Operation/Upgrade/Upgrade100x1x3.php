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
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

class Upgrade100x1x3
{
    private $eavSetup;
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), '100.1.3', '<')) {
            $sales->addColumn(
                $setup->getTable('sales_order'),
                'subscription_parent_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'comment' => 'Parent Subscription ID'
                ]
            );
        }
    }

    public function upgradeData($setup, $context)
    {
        if (version_compare($context->getVersion(), '100.1.3', '<')) {
            $this->showAttributeOnProductListing($setup);
        }
    }

    /**
     * Update Attribute Value
     * `used_in_product_listing` = true
     */
    private function showAttributeOnProductListing($setup)
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributeIds = [
            'subscription_type',
            'billing_period_type',
            'billing_period',
            'define_start_from',
            'allow_trial'
        ];

        foreach ($attributeIds as $attributeId) {
            $this->eavSetup->updateAttribute(Product::ENTITY, $attributeId, 'used_in_product_listing', true);
        }
    }
}
