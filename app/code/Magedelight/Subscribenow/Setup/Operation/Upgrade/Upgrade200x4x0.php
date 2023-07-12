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
 * Class Upgrade200x4x0
 * Mysql Compatibility for Subscribenow PRO reports
 * @package Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class Upgrade200x4x0
{
    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), '200.4.0', '<')) {
            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'product_sku',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 500,
                    'nullable' => true,
                    'comment'  => 'Product Sku',
                    'after'    => 'product_id'
                ]
            );

            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'qty_subscribed',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'length'   => '12,4',
                    'nullable' => false,
                    'default'  => 0,
                    'comment'  => 'Qty Subscribed',
                    'after'    => 'product_sku'
                ]
            );
        }
    }
}
