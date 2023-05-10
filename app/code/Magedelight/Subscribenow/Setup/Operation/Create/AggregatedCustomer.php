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
 * Class AggregatedCustomer
 *
 * Aggregated Customer Table
 * Create table `md_Subscribenow_aggregated_customer`
 *
 * @package Magedelight\Subscribenow\Setup\Operation\Create
 */
class AggregatedCustomer
{
    const TBL = 'md_Subscribenow_aggregated_customer';

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
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            Table::TYPE_DATE,
            null,
            [],
            'Period'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer Id'
        )->addColumn(
            'customer_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Customer Name'
        )->addColumn(
            'customer_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Customer Email'
        )->addColumn(
            'subscriber_count',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Subscriber Count'
        )->addColumn(
            'active_subscriber',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Active Subscriber Count'
        )->addColumn(
            'pause_subscriber',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Pause Subscriber Count'
        )->addColumn(
            'cancel_subscriber',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Cancel Subscriber Count'
        )->addColumn(
            'no_of_occurrence',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'No of occurrence'
        )->addIndex(
            $setup->getIdxName(
                self::TBL,
                ['period', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment('Customer Subscription Aggregated');

        $this->salesConnection->createTable($table);
    }
}
