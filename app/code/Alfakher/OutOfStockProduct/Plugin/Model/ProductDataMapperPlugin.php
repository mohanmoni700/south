<?php
declare (strict_types = 1);

namespace Alfakher\OutOfStockProduct\Plugin\Model;

use Alfakher\OutOfStockProduct\Model\Elasticsearch\Adapter\DataMapper\Stock as StockDataMapper;
use Alfakher\OutOfStockProduct\Model\ResourceModel\Inventory;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;

class ProductDataMapperPlugin
{
    /**
     * @var StockDataMapper
     */
    protected $stockDataMapper;

    /**
     * Inventory Variable
     *
     * @var Inventory
     */
    protected $inventory;
    /**
     * [__construct]
     *
     * @param StockDataMapper $stockDataMapper
     * @param Inventory       $inventory
     */
    public function __construct(StockDataMapper $stockDataMapper, Inventory $inventory)
    {
        $this->stockDataMapper = $stockDataMapper;
        $this->inventory = $inventory;
    }
    /**
     * [afterMap]
     *
     * @param  ProductDataMapper $subject
     * @param  [type]            $documents
     * @param  [type]            $documentData
     * @param  [type]            $storeId
     * @param  [type]            $context
     * @return [type]
     */
    public function afterMap(
        ProductDataMapper $subject,
        $documents,
        $documentData,
        $storeId,
        $context
    ) {
        $this->inventory->saveRelation(array_keys($documents));

        foreach ($documents as $productId => $document) {
      //@codingStandardsIgnoreLine
      $document = array_merge($document, $this->stockDataMapper->map($productId, $storeId));
            $documents[$productId] = $document;
        }

        $this->inventory->clearRelation();

        return $documents;
    }
}
