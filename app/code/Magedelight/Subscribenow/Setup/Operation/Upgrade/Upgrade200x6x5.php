<?php

namespace Magedelight\Subscribenow\Setup\Operation\Upgrade;

use Magedelight\Subscribenow\Setup\Operation\Create\ProductSubscribers;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Upgrade200x6x5
 *
 * Added End Date Subscription Functionality
 * Now admin can allow/disallow Subscription End Date
 * and user can select end date for their subscription from the product detail page.
 *
 * @since 200.6.5
 */
class Upgrade200x6x5
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetup;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetup = $eavSetupFactory;
    }

    public function upgradeSchema($setup)
    {
        $this->addEndDateColumns($setup);
    }

    public function upgradeData($setup)
    {
        $this->addProductAttr($setup);
    }

    private function addProductAttr($setup)
    {
        $this->eavSetup = $this->eavSetup->create(['setup' => $setup]);

        $this->eavSetup->addAttribute(
            Product::ENTITY,
            'allow_subscription_end_date',
            [
                'type' => 'int',
                'label' => 'Show Subscription End Date',
                'input' => 'select',
                'required' => false,
                'global' => Attribute::SCOPE_GLOBAL,
                'group' => 'Subscribe Now',
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'is_used_for_promo_rules' => false,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort' => 92,
                'default' => 0
            ]
        );
    }

    private function addEndDateColumns($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'subscription_end_date',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => true,
                'comment' => 'If admin allowed customer to set end date it will stored date in this column'
            ]
        );
    }
}
