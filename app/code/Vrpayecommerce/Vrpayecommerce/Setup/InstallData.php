<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vrpayecommerce\Vrpayecommerce\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * install the VR pay eCommerce plugin
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface   $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion()) {
            /**
             * Prepare database for install
             */
            $setup->startSetup();

            $statuses = [
                'payment_inreview' => 'In Review',
                'payment_pa' => 'Pre-Authorization of Payment',
                'payment_accepted' => 'Payment Accepted',
            ];
            $table = $setup->getTable('sales_order_status');
            foreach ($statuses as $status => $label) {
                $select = $setup->getConnection()->select()->from(
                    $table,
                    'status'
                )->where(
                    'status=:cache_status'
                )->where(
                    'label=:cache_label'
                );
                $isOnTable = $setup->getConnection()->fetchOne($select, ['cache_status' => $status, 'cache_label' => $label]);
                if (!$isOnTable) {
                    $setup->getConnection()->insert($table, ['status' => $status, 'label' => $label]);
                }
            }

            $states = [
                'payment_inreview' => 'new',
                'payment_pa' => 'new',
                'payment_accepted' => 'processing',
            ];
            $table = $setup->getTable('sales_order_status_state');
            foreach ($states as $status => $state) {
                $select = $setup->getConnection()->select()->from(
                    $table,
                    'status'
                )->where(
                    'status=:cache_status'
                )->where(
                    'state=:cache_state'
                );
                $isOnTable = $setup->getConnection()->fetchOne($select, ['cache_status' => $status, 'cache_state' => $state]);
                if (!$isOnTable) {
                    $setup->getConnection()->insert($table, ['status' => $status, 'state' => $state, 'visible_on_front' => 1]);
                }
            }

            /**
             * Prepare database after install
             */
            $setup->endSetup();
        }
    }
}
