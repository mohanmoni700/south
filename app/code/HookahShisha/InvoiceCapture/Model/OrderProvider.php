<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Order Provider helper for Invoice Capture
 */
class OrderProvider
{
    public const ORDER_STATUS_SHIPPED_CODE = 'shipped';

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
        return $this->collectionFactory->create()
            ->addFieldToFilter(
                ['status'],
                [
                    ['in' => [self::ORDER_STATUS_SHIPPED_CODE]]
                ]
            )
            ->addOrder(OrderInterface::CREATED_AT, SortOrder::SORT_DESC)
            ->setPageSize($pageSize);
    }
}
