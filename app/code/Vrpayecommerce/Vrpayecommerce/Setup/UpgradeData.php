<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Vrpayecommerce
 * @copyright   Copyright (c) 2015 Vrpayecommerce
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Vrpayecommerce\Vrpayecommerce\Setup;
 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $installer = $setup;
        $tableName = array(
                'core_config_data',
                'quote_payment',
                'sales_invoice_grid',
                'sales_order_grid',
                'sales_order_payment'
            );
        

        if (version_compare($context->getVersion(), '2.0.08', '<')) {

            $oldCoreConfigData = array(
                        'payment/vrpayecommerce_klarnainv/active',
                        'payment/vrpayecommerce_klarnainv/server_mode',
                        'payment/vrpayecommerce_klarnainv/channel_id',
                        'payment/vrpayecommerce_klarnainv/merchant_id',
                        'payment/vrpayecommerce_klarnainv/allowspecific',
                        'payment/vrpayecommerce_klarnainv/sort_order',
                        'payment/vrpayecommerce_klarnains/active',
                        'payment/vrpayecommerce_klarnains/server_mode',
                        'payment/vrpayecommerce_klarnains/channel_id',
                        'payment/vrpayecommerce_klarnains/merchant_id',
                        'payment/vrpayecommerce_klarnains/shared_secret',
                        'payment/vrpayecommerce_klarnains/currency',
                        'payment/vrpayecommerce_klarnains/country',
                        'payment/vrpayecommerce_klarnains/language',
                        'payment/vrpayecommerce_klarnains/pclass_id',
                        'payment/vrpayecommerce_klarnains/description',
                        'payment/vrpayecommerce_klarnains/pclass_months',
                        'payment/vrpayecommerce_klarnains/pclass_start_fee',
                        'payment/vrpayecommerce_klarnains/pclass_invoice_fee',
                        'payment/vrpayecommerce_klarnains/pclass_interest_rate',
                        'payment/vrpayecommerce_klarnains/pclass_minimum_purchase',
                        'payment/vrpayecommerce_klarnains/pclass_country',
                        'payment/vrpayecommerce_klarnains/pclass_type',
                        'payment/vrpayecommerce_klarnains/pclass_expiry_date',
                        'payment/vrpayecommerce_klarnains/allowspecific',
                        'payment/vrpayecommerce_klarnains/sort_order',
                        'payment/vrpayecommerce_sofortuberweisung/active',
                        'payment/vrpayecommerce_sofortuberweisung/server_mode',
                        'payment/vrpayecommerce_sofortuberweisung/channel_id',
                        'payment/vrpayecommerce_sofortuberweisung/allowspecific',
                        'payment/vrpayecommerce_sofortuberweisung/sort_order'
                );

            $newCoreConfigData = array(
                        'payment/vrpayecommerce_klarnapaylater/active',
                        'payment/vrpayecommerce_klarnapaylater/server_mode',
                        'payment/vrpayecommerce_klarnapaylater/channel_id',
                        'payment/vrpayecommerce_klarnapaylater/merchant_id',
                        'payment/vrpayecommerce_klarnapaylater/allowspecific',
                        'payment/vrpayecommerce_klarnapaylater/sort_order',
                        'payment/vrpayecommerce_klarnasliceit/active',
                        'payment/vrpayecommerce_klarnasliceit/server_mode',
                        'payment/vrpayecommerce_klarnasliceit/channel_id',
                        'payment/vrpayecommerce_klarnasliceit/merchant_id',
                        'payment/vrpayecommerce_klarnasliceit/shared_secret',
                        'payment/vrpayecommerce_klarnasliceit/currency',
                        'payment/vrpayecommerce_klarnasliceit/country',
                        'payment/vrpayecommerce_klarnasliceit/language',
                        'payment/vrpayecommerce_klarnasliceit/pclass_id',
                        'payment/vrpayecommerce_klarnasliceit/description',
                        'payment/vrpayecommerce_klarnasliceit/pclass_months',
                        'payment/vrpayecommerce_klarnasliceit/pclass_start_fee',
                        'payment/vrpayecommerce_klarnasliceit/pclass_invoice_fee',
                        'payment/vrpayecommerce_klarnasliceit/pclass_interest_rate',
                        'payment/vrpayecommerce_klarnasliceit/pclass_minimum_purchase',
                        'payment/vrpayecommerce_klarnasliceit/pclass_country',
                        'payment/vrpayecommerce_klarnasliceit/pclass_type',
                        'payment/vrpayecommerce_klarnasliceit/pclass_expiry_date',
                        'payment/vrpayecommerce_klarnasliceit/allowspecific',
                        'payment/vrpayecommerce_klarnasliceit/sort_order',
                        'payment/vrpayecommerce_klarnaobt/active',
                        'payment/vrpayecommerce_klarnaobt/server_mode',
                        'payment/vrpayecommerce_klarnaobt/channel_id',
                        'payment/vrpayecommerce_klarnaobt/allowspecific',
                        'payment/vrpayecommerce_klarnaobt/sort_order'

                );
            
            $olQuotePayment = array(
                'vrpayecommerce_klarnainv',
                'vrpayecommerce_klarnains',
                'vrpayecommerce_sofortuberweisung'
            );

            $newQuotePayment = array(
                'vrpayecommerce_klarnapaylater',
                'vrpayecommerce_klarnasliceit',
                'vrpayecommerce_klarnaobt'
            );

            $oldSalesInvoiceGrid = array(
                'vrpayecommerce_klarnainv',
                'vrpayecommerce_klarnains',
                'vrpayecommerce_sofortuberweisung'
            );

            $newSalesInvoiceGrid = array(
                'vrpayecommerce_klarnapaylater',
                'vrpayecommerce_klarnasliceit',
                'vrpayecommerce_klarnaobt'
            );

            $oldSalesOrderGrid = array(
                'vrpayecommerce_klarnainv',
                'vrpayecommerce_klarnains',
                'vrpayecommerce_sofortuberweisung'
            );

            $newSalesOrderGrid = array(
                'vrpayecommerce_klarnapaylater',
                'vrpayecommerce_klarnasliceit',
                'vrpayecommerce_klarnaobt'
            );

            $oldSalesOrderPayment = array(
                'vrpayecommerce_klarnainv',
                'vrpayecommerce_klarnains',
                'vrpayecommerce_sofortuberweisung'
            );

            $newSalesOrderPayment = array(
                'vrpayecommerce_klarnapaylater',
                'vrpayecommerce_klarnasliceit',
                'vrpayecommerce_klarnaobt'
            );

            foreach (array_combine($oldCoreConfigData, $newCoreConfigData) as $old => $new) {
                $installer->updateTableRow(
                    $installer->getTable('core_config_data'),
                    'path',
                    $old,
                    'path',
                    $new
                );
            }

            foreach (array_combine($olQuotePayment, $newQuotePayment) as $old => $new) {
                $installer->updateTableRow(
                    $installer->getTable('quote_payment'),
                    'method',
                    $old,
                    'method',
                    $new
                );
            }

            foreach (array_combine($oldSalesInvoiceGrid, $newSalesInvoiceGrid) as $old => $new) {
                $installer->updateTableRow(
                    $installer->getTable('sales_invoice_grid'),
                    'payment_method',
                    $old,
                    'payment_method',
                    $new
                );
            }

            foreach (array_combine($oldSalesOrderGrid, $newSalesOrderGrid) as $old => $new) {
                $installer->updateTableRow(
                    $installer->getTable('sales_order_grid'),
                    'payment_method',
                    $old,
                    'payment_method',
                    $new
                );
            }

            foreach (array_combine($oldSalesOrderPayment, $newSalesOrderPayment) as $old => $new) {
                $installer->updateTableRow(
                    $installer->getTable('sales_order_payment'),
                    'method',
                    $old,
                    'method',
                    $new
                );
            }
        }
    
        $setup->endSetup();
    }
}