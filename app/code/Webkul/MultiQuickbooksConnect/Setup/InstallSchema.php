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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /*
         * Create table 'multi_quickbook_map_sales_receipt'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('multi_quickbook_map_sales_receipt'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'mage_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento Order Id'
            )->addColumn(
                'mage_invoice_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Magento Invoice Id'
            )->addColumn(
                'quickbook_sales_receipt_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Quickbook Sales Receipt Id'
            )->addColumn(
                'quickbook_sales_doc_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Quickbook Sales Receipt Doc Number'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'Order sync time'
            )->addIndex(
                $installer->getIdxName('multi_quickbook_map_sales_receipt', ['entity_id']),
                ['entity_id']
            )->setComment('Quickbook Map Sales Receipt');

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
        $this->addForeignKeys($setup);
    }

    public function addForeignKeys($setup)
    {
        /**
         * Add foreign keys for table wk_amazon_mapped_product
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'multi_quickbook_map_sales_receipt',
                'mage_order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('multi_quickbook_map_sales_receipt'),
            'mage_order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
