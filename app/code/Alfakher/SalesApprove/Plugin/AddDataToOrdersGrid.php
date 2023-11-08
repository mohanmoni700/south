<?php

declare(strict_types=1);

namespace Alfakher\SalesApprove\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

/**
 * Class AddDataToOrdersGrid
 */
class AddDataToOrdersGrid
{
    /**
     * @param CollectionFactory $subject
     * @param Collection $collection
     * @param $requestName
     * @return Collection
     */
    public function afterGetReport(CollectionFactory $subject, Collection $collection, $requestName): Collection
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }
        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            $collection->getSelect()->joinLeft(
                ['signifyd_connect_case' => $collection->getTable('signifyd_connect_case')],
                'main_table.entity_id = signifyd_connect_case.order_id',
                ['signifyd_score' => 'score', 'signifyd_guarantee' => 'guarantee']
            );
        }
        return $collection;
    }
}
