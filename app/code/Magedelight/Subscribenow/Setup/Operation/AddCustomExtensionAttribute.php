<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class AddCustomExtensionAttribute
 * Add Extension Custom Attribute for sales
 * @package Magedelight\Subscribenow\Setup\Operation\Create
 */
class AddCustomExtensionAttribute
{
    private $salesConnection;
    private $checkoutConnection;

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup, $sales, $checkout)
    {
        $this->salesConnection = $sales;
        $this->checkoutConnection = $checkout;
        $this->addCustomExtensionAttribute($setup);
    }

    private function addCustomExtensionAttribute($setup)
    {
        $this->setupSalesOrderAttribute($setup);
        $this->setupQuoteAttribute($setup);
        $this->setupQuoteAddressAttribute($setup);
        $this->setupSalesInvoiceAttribute($setup);
        $this->setupSalesCreditMemoAttribute($setup);
        $this->setupQuoteItemAttribute($setup);
        $this->setupSalesOrderItemAttribute($setup);
    }

    private function setCustomAttributeColumns($table, $connection)
    {
        if (!$table) {
            return;
        }

        $colums = [
            'subscribenow_init_amount' => [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                [],
                'comment' => 'Subscription Initial Amount'
            ],
            'base_subscribenow_init_amount' => [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                [],
                'comment' => 'Base Subscription Initial Amount'
            ],
            'subscribenow_trial_amount' => [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                [],
                'comment' => 'Subscription Trial Amount'
            ],
            'base_subscribenow_trial_amount' => [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                [],
                'comment' => 'Base Subscription Trial Amount'
            ]
        ];

        foreach ($colums as $column => $data) {
            $connection->addColumn($table, $column, $data);
        }
    }

    /**
     * Add Custom Attribute in `sales_order`
     *
     * @return void
     */
    private function setupSalesOrderAttribute($setup)
    {
        $table = $setup->getTable('sales_order');
        $this->setCustomAttributeColumns($table, $this->salesConnection);

        $this->salesConnection->addColumn(
            $table,
            'has_trial',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => '6',
                ['unsigned' => true, 'nullable' => true],
                'comment' => 'Subscription Initial Amount'
            ]
        );
    }

    /**
     * Add Custom Attribute in `quote`
     *
     * @return void
     */
    private function setupQuoteAttribute($setup)
    {
        $table = $setup->getTable('quote');

        $this->setCustomAttributeColumns($table, $this->checkoutConnection);

        $this->checkoutConnection->addColumn(
            $table,
            'md_cron_order',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => '6',
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'comment' => 'Subscription cron order flag',
            ]
        );

        $this->checkoutConnection->addColumn(
            $table,
            'md_trial_set',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => '6',
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'comment' => 'Subscription trial order flag',
            ]
        );
    }

    /**
     * Add Custom Attribute in `quote_address`
     *
     * @return void
     */
    private function setupQuoteAddressAttribute($setup)
    {
        $table = $setup->getTable('quote_address');
        $this->setCustomAttributeColumns($table, $this->checkoutConnection);
    }

    /**
     * Add Custom Attribute in `sales_invoice`
     *
     * @return void
     */
    private function setupSalesInvoiceAttribute($setup)
    {
        $table = $setup->getTable('sales_invoice');
        $this->setCustomAttributeColumns($table, $this->salesConnection);
    }

    /**
     * Add Custom Attribute in `sales_creditmemo`
     *
     * @return void
     */
    private function setupSalesCreditMemoAttribute($setup)
    {
        $table = $setup->getTable('sales_creditmemo');
        $this->setCustomAttributeColumns($table, $this->salesConnection);
    }

    /**
     * Custom Attribute for Sales/Address/Quote/Invoice
     *
     * @param mixed $table
     * @return void
     */
    private function setCustomAttributeColumnsQuoteItem($table, $connection)
    {
        if (!$table) {
            return;
        }

        $columns = [
            'md_item_org_price' => [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                [],
                'comment' => 'Future date subscription price'
            ],
            'is_subscription' => [
                'type' => Table::TYPE_SMALLINT,
                'length' => '5',
                [],
                'comment' => 'Item subscription flag'
            ]
        ];

        foreach ($columns as $column => $data) {
            $connection->addColumn($table, $column, $data);
        }
    }

    /**
     * Add Custom Attribute in `quote_item`
     *
     * @return void
     */
    private function setupQuoteItemAttribute($setup)
    {
        $table = $setup->getTable('quote_item');
        $this->setCustomAttributeColumnsQuoteItem($table, $this->checkoutConnection);
    }

    /**
     * Add Custom Attribute in `sales_order_item`
     *
     * @return void
     */
    private function setupSalesOrderItemAttribute($setup)
    {
        $table = $setup->getTable('sales_order_item');
        $this->setCustomAttributeColumnsQuoteItem($table, $this->salesConnection);
    }
}
