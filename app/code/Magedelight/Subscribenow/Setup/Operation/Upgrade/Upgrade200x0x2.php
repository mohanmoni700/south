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

class Upgrade200x0x2
{
    public function upgradeSchema($setup, $context, $sales)
    {
        if (version_compare($context->getVersion(), '200.0.2', '<')) {
            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'product_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Product Name'
                ]
            );
            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'payment_title',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Payment Method Title'
                ]
            );
        }
    }
}
