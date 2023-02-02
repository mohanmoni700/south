<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model;

class ProductAssociatedOrders extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders');
    }

    /**
     * Load ProductAssociatedOrders by system orderid identifier.
     *
     * @param string $orderId
     *
     * @return \Magedelight\Subscribenow\Model\ProductAssociatedOrders
     */
    public function loadByOrderId($orderId)
    {
        return $this->loadByAttribute('order_id', $orderId);
    }

    /**
     * Load ProductAssociatedOrders by custom attribute value. Attribute value should be unique.
     *
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    public function loadByAttribute($attribute, $value)
    {
        $this->load($value, $attribute);

        return $this;
    }
}
