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

namespace Magedelight\Subscribenow\Setup\Operation\Upgrade;

use Magedelight\Subscribenow\Setup\Operation\Create\ProductSubscribers;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Upgrade200x5x0
 * Added Billing Frequency Update Feature
 * @package Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class Upgrade200x5x0
{
    const VERSION = '200.5.0';

    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), self::VERSION, '<')) {
            $this->removeOccurrenceTable($setup, $sales);
            $this->addColumns($setup, $sales);
        }
    }

    public function upgradeData($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), self::VERSION, '<')) {
            $this->updateBillingFrequency($setup, $sales);
        }
    }

    private function addColumns($setup, $sales)
    {
        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'is_update_billing_frequency',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'default' => false,
                'comment' => 'Can Update Billing Frequency?'
            ]
        );
        $sales->addColumn(
            $setup->getTable(ProductSubscribers::TBL),
            'billing_frequency_cycle',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 120,
                'nullable' => true,
                'comment' => 'Config Billing Frequency Cycle'
            ]
        );
    }

    /**
     * As `md_Subscribenow_product_occurrence`
     * is no longer used since from 200.0.0
     *
     * @param $setup
     */
    private function removeOccurrenceTable($setup, $sales)
    {
        $table = $setup->getTable('md_Subscribenow_product_occurrence');
        if ($sales->isTableExists($table)) {
            $sales->dropTable($table);
        }
    }

    private function updateBillingFrequency($setup, $sales)
    {
        $table = $setup->getTable(ProductSubscribers::TBL);
        $wh = 'billing_frequency_cycle IS NULL';
        $select = $sales->select()->from($table, ['subscription_id', 'order_item_info'])->where($wh);
        $query = $sales->query($select);

        if ($sales->fetchCol($select)) {
            while ($row = $query->fetch()) {
                $itemInfo = json_decode($row['order_item_info'], true);
                $billingPeriod = $itemInfo && !empty($itemInfo['billing_period']) ? $itemInfo['billing_period'] : null;

                if ($billingPeriod) {
                    $sales->update(
                        $table,
                        [
                            'billing_frequency_cycle' => $billingPeriod,
                            'is_update_billing_frequency' => true
                        ],
                        $wh
                    );
                }
            }
        }
    }
}
