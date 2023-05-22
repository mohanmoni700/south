<?php

namespace Avalara\Excise\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $exciseLog = "excise_log";
            /**
             * Create table 'excise_log'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable($exciseLog)
                )
                ->addColumn(
                    'log_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'Log ID'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Log Time'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Store ID'
                )
                ->addColumn(
                    'level',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [],
                    'Log Level'
                )
                ->addColumn(
                    'message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Message Being Logged'
                )
                ->addColumn(
                    'source',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Code Source Reference'
                )
                ->addColumn(
                    'request',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Request'
                )
                ->addColumn(
                    'result',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Result'
                )
                ->addColumn(
                    'additional',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Additional'
                )
                ->addIndex(
                    $installer->getIdxName(
                        $exciseLog,
                        ['created_at'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['created_at'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $installer->getIdxName(
                        $exciseLog,
                        [
                            'level',
                            'created_at'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [
                        'level',
                        'created_at'
                    ],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->setComment('Excise Log Table');
            $installer->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $exciseQueue = "excise_queue";
            /**
             * Create table 'excise_log'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable($exciseQueue)
                )
                ->addColumn(
                    'queue_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'Queue ID'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Queue Time'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Updated Time'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Store ID'
                )
                ->addColumn(
                    'entity_type_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Entity Type ID'
                )
                ->addColumn(
                    'entity_type_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [],
                    'Entity Type Code"'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Entity ID'
                )
                ->addColumn(
                    'increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [],
                    'Increment ID'
                )
                ->addColumn(
                    'queue_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    [],
                    'Queue Status'
                )
                ->addColumn(
                    'attempts',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Processing Attempts'
                )
                ->addColumn(
                    'message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Message'
                )
                ->addIndex(
                    $installer->getIdxName(
                        $exciseQueue,
                        [
                            'entity_type_id',
                            'entity_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [
                        'entity_type_id',
                        'entity_id'
                    ],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->setComment('Excise Queue Table');
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $connection = $installer->getConnection();
            $salesOrderAddress = $installer->getTable('sales_order_address');
            if ($connection->tableColumnExists($salesOrderAddress, 'county') === false) {
                $connection->addColumn($salesOrderAddress, 'county', [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'after'     => null,
                        'comment'   => 'County'
                ]);
            }
        }
        // code to add table for entity use codes
        if (version_compare($context->getVersion(), '0.0.15', '<')) {
            $exciseEntityUseCode = "excise_entity_use_code";
            /**
             * Create table 'excise_entity_use_code'
             */
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable($exciseEntityUseCode)
                )
                ->addColumn(
                    'entity_use_code_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'Entity use code id'
                )
                ->addColumn(
                    'code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Entity use code label'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Entity use code value'
                )               
                ->setComment('Excise Use Code Table');
            $installer->getConnection()->createTable($table);
        }
    }
}
