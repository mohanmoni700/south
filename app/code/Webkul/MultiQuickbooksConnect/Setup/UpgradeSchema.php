<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create table 'multi_quickbook_accounts'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('multi_quickbook_accounts'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Entity Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Quickbook account name'
            )->addColumn(
                'sales_receipt_create_on',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Sales Receipt Create On Quickbook'
            )->addColumn(
                'us_store',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'QuickBooks US Store'
            )->addColumn(
                'asset_account',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Inventory Other Asset Account'
            )->addColumn(
                'income_account',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Income Account'
            )->addColumn(
                'expense_account',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Expense Account'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'status of account'
            )->addColumn(
                'is_authenticated',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'status of account authentication'
            )->addColumn(
                'is_current',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Set current flag'
            )->addColumn(
                'oauth2_access_token',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2056,
                [],
                'Access Token'
            )->addColumn(
                'oauth2_access_token_expire_on',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Access Token Expire On'
            )->addColumn(
                'oauth2_refresh_token',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2056,
                [],
                'Refresh Token'
            )->addColumn(
                'oauth2_refresh_token_expire_on',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Refresh Token Expire On'
            )->addColumn(
                'realm_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Realm ID'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'account created time'
            )->addIndex(
                $setup->getIdxName('multi_quickbook_accounts', ['entity_id']),
                ['entity_id']
            )->setComment('Quickbook Accounts');

            $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable('multi_quickbook_map_creditmemo'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'Entity Id'
            )->addColumn(
                'mage_creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Credit Memo Id'
            )->addColumn(
                'quickbook_creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Quickbook Credit Memo Id'
            )->addColumn(
                'quickbook_creditmemo_doc_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Quickbook Credit Memo Doc Number'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Associated Qb Account Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Credit Memo sync time'
            )->addIndex(
                $setup->getIdxName('multi_quickbook_map_creditmemo', ['entity_id']),
                ['entity_id']
            )->setComment('Quickbook Map Credit Memo');

            $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable('multi_quickbook_map_sales_receipt'),
            'account_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Associated Qb Account Id',
                'after' => 'quickbook_sales_doc_number'
            ]
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'multi_quickbook_map_sales_receipt',
                'account_id',
                'multi_quickbook_accounts',
                'entity_id'
            ),
            $setup->getTable('multi_quickbook_map_sales_receipt'),
            'account_id',
            $setup->getTable('multi_quickbook_accounts'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'multi_quickbook_map_creditmemo',
                'mage_creditmemo_id',
                'sales_creditmemo',
                'entity_id'
            ),
            $setup->getTable('multi_quickbook_map_creditmemo'),
            'mage_creditmemo_id',
            $setup->getTable('sales_creditmemo'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'multi_quickbook_map_creditmemo',
                'account_id',
                'multi_quickbook_accounts',
                'entity_id'
            ),
            $setup->getTable('multi_quickbook_map_creditmemo'),
            'account_id',
            $setup->getTable('multi_quickbook_accounts'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('multi_quickbook_accounts'),
            'creditmemo_auto_sync',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'comment' => 'Credit Memo Auto Sync',
                'after' => 'sales_receipt_create_on'
            ]
        );
    }
}
