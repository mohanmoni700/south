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

namespace Magedelight\Subscribenow\Setup\Operation\Create;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class ProductSubscribers
 *
 * Product Subscription Table
 * Create table `md_Subscribenow_product_subscribers`
 *
 * @package Magedelight\Subscribenow\Setup\Operation\Create
 */
class ProductSubscribers
{
    const TBL = 'md_Subscribenow_product_subscribers';

    private $salesConnection;

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup, $sales)
    {
        $this->salesConnection = $sales;
        $this->createTable($setup);
    }

    private function createTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(self::TBL);
        $table = $this->salesConnection->newTable($tableName);

        $table->addColumn(
            'subscription_id',
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Unique id for subscribers'
        )->addColumn(
            'profile_id',
            Table::TYPE_TEXT,
            150,
            ['nullable' => false],
            'Subscription profile ID, Unique key'
        )->addIndex(
            $setup->getIdxName(
                $tableName,
                ['profile_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['profile_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer id of subscriber for website'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Subscribed product id'
        )->addColumn(
            'subscriber_name',
            Table::TYPE_TEXT,
            150,
            ['nullable' => false],
            'Subscriber full name'
        )->addColumn(
            'subscriber_email',
            Table::TYPE_TEXT,
            150,
            ['nullable' => false],
            'Customer email adress'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store id from which subscriber has subscribed plan.'
        )->addColumn(
            'payment_method_code',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Payment Method code'
        )->addColumn(
            'subscription_start_date',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'subscription start date for subscriber'
        )->addColumn(
            'suspension_threshold',
            Table::TYPE_SMALLINT,
            5,
            ['nullable' => false],
            'limit for failure of payment till profile can active'
        )->addColumn(
            'billing_period_label',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Product Subscription billing period label'
        )->addColumn(
            'billing_period',
            Table::TYPE_SMALLINT,
            2,
            ['nullable' => false],
            'Product subscription billing period'
        )->addColumn(
            'billing_frequency',
            Table::TYPE_SMALLINT,
            5,
            ['nullable' => false],
            'Product subscription period frequency which defines once cycle'
        )->addColumn(
            'period_max_cycles',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Subscription period max cycles to be repeated'
        )->addColumn(
            'billing_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Total billing amount for subscriber'
        )->addColumn(
            'trial_period_label',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Product Subscription billing period label'
        )->addColumn(
            'trial_period_unit',
            Table::TYPE_SMALLINT,
            6,
            ['nullable' => false],
            'Product subscription trial billing period'
        )->addColumn(
            'trial_period_frequency',
            Table::TYPE_SMALLINT,
            6,
            ['nullable' => false],
            'Product subscription trial period frequency'
        )->addColumn(
            'trial_period_max_cycle',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Product subscription trial period max cycles to be repeated'
        )->addColumn(
            'trial_billing_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Product subscription trial period billing amount'
        )->addColumn(
            'currency_code',
            Table::TYPE_TEXT,
            3,
            ['nullable' => false],
            'Subscription order currency code'
        )->addColumn(
            'shipping_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subscription order shipping amount'
        )->addColumn(
            'tax_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subscription order tax amount'
        )->addColumn(
            'initial_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subscription order initial amount'
        )->addColumn(
            'discount_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subscription order discount amount'
        )->addColumn(
            'order_info',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Subscription order info'
        )->addColumn(
            'order_item_info',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Order item info'
        )->addColumn(
            'billing_address_info',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Order billing information for customer'
        )->addColumn(
            'shipping_address_info',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Order shipping information for subscriber'
        )->addColumn(
            'additional_info',
            Table::TYPE_TEXT,
            null,
            [],
            'Subscriber related additional information'
        )->addColumn(
            'subscription_status',
            Table::TYPE_SMALLINT,
            6,
            ['nullable' => false],
            'Subscription status'
        )->addColumn(
            'initial_order',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Initial Order Id'
        )->addColumn(
            'subscription_item_info',
            Table::TYPE_TEXT,
            null,
            [],
            'Subscriber Item Info'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Subscription created at'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Subscription updated at'
        );

        $this->salesConnection->createTable($table);
    }
}
