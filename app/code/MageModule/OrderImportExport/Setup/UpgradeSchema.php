<?php

namespace MageModule\OrderImportExport\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package MageModule\OrderImportExport\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '1.2.1') < 0) {
            /**
             * Create table 'magemodule_order_import_export_config'
             */
            $table = $connection->newTable('magemodule_order_import_export_config')
                ->addColumn(
                    'type',
                    DdlTable::TYPE_TEXT,
                    '6',
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'config',
                    DdlTable::TYPE_TEXT,
                    '64k',
                    ['nullable' => true],
                    'Config'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $connection->getTableName('magemodule_order_import_export_config'),
                        ['type'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['type'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Order Import/Export Config Table');
            $connection->createTable($table);
        }
    }
}
