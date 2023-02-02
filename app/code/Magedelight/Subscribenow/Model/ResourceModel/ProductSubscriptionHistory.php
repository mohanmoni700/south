<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

class ProductSubscriptionHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        if (!$connectionName) {
            $connectionName = 'sales';
        }
        
        parent::__construct($context, $connectionName);
    }

    /**
     * Model Initialization.
     */
    protected function _construct()
    {
        $this->_init('md_subscribenow_product_subscription_history', 'hid');
    }
}
