<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductAlertStockGuestUser extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_alert_stock_guest_user', 'alert_stock_id');
    }
}
