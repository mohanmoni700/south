<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vrpayecommerce\Vrpayecommerce\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * install the VR pay eCommerce schema
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion()) {
	        /**
	         * Prepare database for install
	         */
	        $setup->startSetup();

	        /**
	         * Create table 'vrpayecommerce_payment_information'
	         */
	        $table = $setup->getConnection()->newTable(
	            $setup->getTable('vrpayecommerce_payment_information')
	        )->addColumn(
	            'information_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	            null,
	            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	            'Information Id'
	        )->addColumn(
	            'customer_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	            null,
	            ['unsigned' => true, 'nullable' => false],
	            'Customer Id'
	        )->addColumn(
	            'payment_group',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            6,
	            ['nullable' => false],
	            'Payment Group'
	        )->addColumn(
	            'server_mode',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Server Mode'
	        )->addColumn(
	            'channel_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            32,
	            ['nullable' => false],
	            'Channel Id'
	        )->addColumn(
	            'registration_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            32,
	            ['nullable' => false],
	            'Registration Id'
	        )->addColumn(
	            'brand',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => false],
	            'Brand'
	        )->addColumn(
	            'holder',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => false],
	            'Holder'
	        )->addColumn(
	            'email',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            60,
	            ['nullable' => false],
	            'Email'
	        )->addColumn(
	            'last_4digits',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Last 4 Digits'
	        )->addColumn(
	            'expiry_month',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            2,
	            ['nullable' => false],
	            'Expiry Month'
	        )->addColumn(
	            'expiry_year',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Expiry Year'
	        )->addColumn(
	            'payment_default',
	            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
	            null,
	            ['nullable' => false, 'default' => '0'],
	            'Payment Default'
	        );
	        $setup->getConnection()->createTable($table);

	        /**
	         * Prepare database after install
	         */
	        $setup->endSetup();
	    }
    }
}
