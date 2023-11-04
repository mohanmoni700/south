<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Block\Product\View;

/**
 * Recurring payment view stock
 *
 * @api
 * @since 100.0.2
 */
class Stock extends \Magento\ProductAlert\Block\Product\View\Stock
{
    /**
     * Get current product id
     *
     * @return mixed
     */
    public function getCurrentProductId()
    {
        return $this->getProduct()->getEntityId();
    }
}
