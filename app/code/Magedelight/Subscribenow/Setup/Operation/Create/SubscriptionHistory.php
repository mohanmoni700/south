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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class SubscriptionHistory
{
    const TBL = 'md_Subscribenow_product_subscription_history';

    private $salesConnection;
    private $checkoutConnection;

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
            'hid',
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'History id'
        )->addColumn(
            'subscription_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Product Subscription id'
        )->addColumn(
            'modify_by',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Subscription Affect By'
        )->addColumn(
            'comment',
            Table::TYPE_TEXT,
            null,
            [],
            'Product Subscription Comment'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Subscription created at'
        )->addForeignKey(
            self::TBL . '_subscription_id',
            'subscription_id',
            $setup->getTable(ProductSubscribers::TBL),
            'subscription_id',
            Table::ACTION_CASCADE
        );

        $this->salesConnection->createTable($table);
    }
}
