<?php

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
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }
        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            $collection->getSelect()->joinLeft(
                ['signifyd_connect' => $collection->getTable('signifyd_connect_case')],
                'main_table.entity_id = signifyd_connect.order_id',
                ['signifyd_score' => 'score', 'signifyd_guarantee' => 'guarantee']
            );
        }
        return $collection;
    }
}
