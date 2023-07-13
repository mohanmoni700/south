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

class Upgrade100x1x4
{
    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), '100.1.4', '<')) {
            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'base_currency_code',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 3,
                    'comment' => 'Base Currency Code'
                ]
            );

            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'base_billing_amount',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => 0,
                    'length' => '10,4',
                    'comment' => 'Base Billing Amount'
                ]
            );

            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'base_trial_billing_amount',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => 0,
                    'length' => '10,4',
                    'comment' => 'Base Trial Billing Amount'
                ]
            );

            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'base_initial_amount',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'default' => 0,
                    'length' => '10,4',
                    'comment' => 'Base Initial Amount'
                ]
            );
        }
    }
}
