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

/**
 * Class AssociateOrders
 *
 * Associate Product Order Table
 * Create table `md_Subscribenow_product_associated_orders`
 *
 * @package Magedelight\Subscribenow\Setup\Operation\Create
 */
class AssociateOrders
{
    const TBL = 'md_Subscribenow_product_associated_orders';

    private $salesConnection;

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup, $sales)
    {
        $this->salesConnection = $sales;
        $this->createTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(self::TBL);
        $table = $this->salesConnection->newTable($tableName);

        $table->addColumn(
            'relation_id',
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Subscription relation unique id'
        )->addColumn(
            'subscription_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'Product Subscription id'
        )->addColumn(
            'order_id',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'subscription order id'
        )->addForeignKey(
            self::TBL.'_subscription_id',
            'subscription_id',
            $setup->getTable(ProductSubscribers::TBL),
            'subscription_id',
            Table::ACTION_CASCADE
        );

        $this->salesConnection->createTable($table);
    }
}
