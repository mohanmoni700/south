<?php
/**
 * @author:CORRA
 */
declare(strict_types=1);

namespace HookahShisha\Order\Plugin;

use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

class GridJoinCollection
{
    /**
     * Joining table get 'alfa_consent' value from order table.
     *
     * @param CollectionFactory $subject
     * @param Collection $collection
     * @param string $requestName
     * @return Collection
     */
    public function afterGetReport(
        CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        if ($requestName == 'sales_order_grid_data_source') {
            $select = $collection->getSelect();
            $select->joinLeft(
                ["secondTable" => $collection->getTable("sales_order")],
                'main_table.increment_id = secondTable.increment_id',
                ['alfa_consent']
            );
        }   return $collection;
    }
}
