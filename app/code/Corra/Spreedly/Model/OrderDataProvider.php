<?php

declare(strict_types=1);

namespace Corra\Spreedly\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Order Data Provider for Getting CurrentDate Order
 */
class OrderDataProvider
{
    private const SIGNIFYD_GUARANTEE = 'APPROVED';
    private const SIGNIFYD_ACCEPT = 'ACCEPT';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get size of CurrentDate Order collection
     *
     * @return int
     */
    public function getTodayOrdersCount()
    {
        $currentDate = date('Y-m-d');
        $collection =  $this->collectionFactory->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToFilter('created_at', ['gteq'=>$currentDate.' 00:00:00'])
            ->addAttributeToFilter('created_at', ['lteq'=>$currentDate.' 23:59:59']);
        $collection->getSelect()
            ->joinLeft(
                ['scc'=>'signifyd_connect_case'],
                'scc.order_increment=main_table.increment_id',
                'order_increment'
            )
            ->join(
                ['sop'=>'sales_order_payment'],
                'sop.parent_id=main_table.entity_id',
                'parent_id'
            )
            ->where('scc.guarantee IN (\''.self::SIGNIFYD_ACCEPT .'\', \''. self::SIGNIFYD_GUARANTEE. '\') OR scc.order_increment IS NULL')
            ->where(
                '(sop.method=\'spreedly\')'
            );
        return $collection->getSize();
    }
}
