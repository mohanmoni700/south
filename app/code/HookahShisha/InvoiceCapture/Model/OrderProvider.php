<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Order Provider helper for Invoice Capture
 */
class OrderProvider
{
    protected const SIGNIFYD_GUARANTEE = 'APPROVED';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * constructor.
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    /**
     * Get eligible orders
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getEligibleOrders()
    {
        //Take config values
        $pageSize = $this->config->getBatchSize();
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(['entity_id', 'status']);
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }
        $collection
            ->getSelect()
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
            ->where('scc.guarantee=? OR scc.order_increment IS NULL', self::SIGNIFYD_GUARANTEE)
            ->where(
                '(sop.method=\'free\' AND main_table.status=\'pending\') OR' .
                '(main_table.status IN (\'processing\'))'
            );
        return $collection;
    }
}
