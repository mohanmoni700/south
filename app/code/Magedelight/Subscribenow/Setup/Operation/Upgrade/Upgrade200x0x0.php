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

use Magedelight\Subscribenow\Setup\Operation\Create\ProductSubscribers;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityAttribute;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Upgrade200x0x0
 * Major Version Release with refactoring whole code base
 * @package Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class Upgrade200x0x0
{
    const TBL_PRODUCT_SUBSCRIBER_BACKUP = 'md_Subscribenow_product_subscribers_100x1x3';

    public function __construct(
        EntityAttribute $eavAttribute
    ) {
        $this->eavAttribute = $eavAttribute;
    }

    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), '200.0.0', '<')) {
            if ($context->getVersion()) {
                $this->createBackup($setup, $sales);
            }

            $this->addColumns($setup, $sales);
        }
    }

    public function upgradeData($setup, $context)
    {
        if (version_compare($context->getVersion(), '200.0.0', '<')) {
            $this->upgradeProductTrialAttribute($setup);
        }
    }

    private function createBackup($setup, $sales)
    {
        $table = $setup->getTable(ProductSubscribers::TBL);
        $backup_table = $setup->getTable(self::TBL_PRODUCT_SUBSCRIBER_BACKUP);

        $sales->query("CREATE TABLE `$backup_table` LIKE `$table`");
        $sales->query("INSERT `$backup_table` SELECT * FROM `$table`");
    }

    private function addColumns($setup, $sales)
    {
        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'last_bill_date',
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Last Billing Paid Date'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'trial_count',
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'comment' => 'Trial Count'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'is_trial',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'comment' => 'Is Trial'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'shipping_method_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'Shipping Method Code'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'payment_token',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Payment Token'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'total_bill_count',
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'comment' => 'Total Bill Count'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'default' => 0,
                'length' => '10,4',
                'comment' => 'Base Shipping Amount'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'default' => 0,
                'length' => '10,4',
                'comment' => 'Base Tax Amount'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'nullable' => true,
                'default' => 0,
                'length' => '10,4',
                'comment' => 'Base Discount Amount'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'initial_order_id',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'length' => '32',
                'comment' => 'Initial Order ID'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'billing_address_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => '11',
                'comment' => 'Billing Address ID'
            ]
        );

        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'shipping_address_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => '11',
                'comment' => 'Shipping Address ID'
            ]
        );

        $sales->changeColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'next_occurrence_date',
            'next_occurrence_date',
            ['type' => Table::TYPE_DATETIME,'nullable' => false,'comment' => "Next Occurence Date"]
        );

        $sales->changeColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'created_at',
            'created_at',
            ['type' => Table::TYPE_DATETIME,'nullable' => false,'comment' => "Subscription created at"]
        );

        $sales->changeColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'updated_at',
            'updated_at',
            ['type' => Table::TYPE_DATETIME,'nullable' => false,'comment' => "Subscription updated at"]
        );

        $sales->changeColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'subscription_start_date',
            'subscription_start_date',
            ['type' => Table::TYPE_DATETIME,'nullable' => false,'comment' => "Subscription Start Date"]
        );

        $sales->changeColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'last_bill_date',
            'last_bill_date',
            ['type' => Table::TYPE_DATETIME,'nullable' => true,'comment' => "Last Billing Paid Date"]
        );
    }

    private function upgradeProductTrialAttribute($setup)
    {
        $attribute_id = $this->eavAttribute->getIdByCode('catalog_product', 'allow_trial');
        $table = $setup->getTable('catalog_product_entity_int');
        $setup->getConnection()->query("UPDATE `$table` SET `value` = IF (`value`, 0, 1) WHERE `attribute_id` = '$attribute_id'");
    }
}
