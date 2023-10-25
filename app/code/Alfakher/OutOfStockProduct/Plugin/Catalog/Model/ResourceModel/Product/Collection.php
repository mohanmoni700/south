<?php

declare(strict_types=1);

namespace Alfakher\OutOfStockProduct\Plugin\Catalog\Model\ResourceModel\Product;

class Collection
{
    /**
     * Around plugin to sort OOS Bundle products to last
     *
     * @param $subject
     * @param $proceed
     * @param string $attribute
     * @param string $dir
     * @return mixed
     */
    public function aroundAddAttributeToSort($subject, $proceed, $attribute, $dir = \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
    {
        if ($attribute == 'stock_status' && $dir == \Magento\Framework\Data\Collection::SORT_ORDER_ASC) {
            $subject->getSelect()->joinLeft(
                ['stock_index' => $subject->getTable('cataloginventory_stock_status')],
                'e.entity_id = stock_index.product_id',
                []
            )->where(
                'stock_index.stock_status = 0'
            )->order('stock_index.stock_status DESC');
        }

        return $proceed($attribute, $dir);
    }
}
