<?php
declare (strict_types = 1);

namespace Alfakher\OutOfStockProduct\Model\Elasticsearch\Adapter\DataMapper;

use Alfakher\OutOfStockProduct\Model\ResourceModel\Inventory;
use Magento\Store\Model\StoreManagerInterface;

class Stock
{
    /**
     * @var Inventory
     */
    private $inventory;

    /**
     *  StoreManagerInterface variable
     *
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * [__construct]
     *
     * @param Inventory             $inventory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Inventory $inventory,
        StoreManagerInterface $storeManager
    ) {
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
    }
    /**
     * [map]
     *
     * @param  [type] $entityId
     * @param  [type] $storeId
     * @return [type]
     */
    public function map($entityId, $storeId): array
    {
        $sku = $this->inventory->getSkuRelation((int) $entityId);

        if (!$sku) {
            return ['out_of_stock_at_last' => true];
        }

        $value = $this->inventory->getStockStatus(
            $sku,
            $this->storeManager->getStore($storeId)->getWebsite()->getCode()
        );

        return ['out_of_stock_at_last' => $value];
    }
}
