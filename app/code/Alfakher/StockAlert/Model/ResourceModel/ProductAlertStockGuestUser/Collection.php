<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Alfakher\StockAlert\Model\ProductAlertStockGuestUser;
use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser as ProductAlertStockGuestUserResource;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'alert_stock_id';

    /**
     * Collection Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ProductAlertStockGuestUser::class,
            ProductAlertStockGuestUserResource::class
        );
    }
}
