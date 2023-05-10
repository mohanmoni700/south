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
 * Class Upgrade200x3x0
 * Group Product Compatibility
 * @package Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class Upgrade200x3x0
{
    public function upgradeSchema($setup, $context, $sales)
    {

        if (version_compare($context->getVersion(), '200.3.0', '<')) {
            $sales->addColumn(
                $setup->getTable(ProductSubscribers::TBL),
                'parent_product_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'comment' => 'Subscription Product(s) Parent ID'
                ]
            );
        }
    }
}
