<?php
namespace Alfakher\FinanceVerified\Model\Plugin\Sales\Order;

class Grid
{

    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'customer_grid_flat';

    public function afterSearch($intercepter, $collection)
    {

        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['co' => $leftJoinTableName],
                    "co.entity_id = main_table.customer_id",
                    [
                        'isfinance_verified' => 'co.isfinance_verified',
                    ]
                );
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);

        }
        return $collection;

    }

}
