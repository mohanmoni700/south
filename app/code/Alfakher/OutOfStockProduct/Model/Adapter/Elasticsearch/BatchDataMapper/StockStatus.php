<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Model\Adapter\Elasticsearch\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;

class StockStatus implements AdditionalFieldsProviderInterface
{
    /**
     * @var StockItemResource
     */
    private StockItemResource $stockItemResource;

   /**
    * @param StockItemResource $stockItemResource
    */
    public function __construct(
        StockItemResource $stockItemResource
    )
    {
        $this->stockItemResource = $stockItemResource;
    }

    /**
     * @inheritDoc
     */
    public function getFields(array $productIds, $storeId): array
    {
        $stockStatusMapping = $this->getStockStatusMapping($productIds);
        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = [
                'stock_status' => $stockStatusMapping[$productId] ?? 0
            ];
        }
        return $fields;
    }

    /**
     * @param array $productIds
     * @return array
     */
    protected function getStockStatusMapping(array $productIds): array
    {
        $stockStatusMapping = [];
        $connection = $this->stockItemResource->getConnection();
        $query = $connection->select()
            ->from($this->stockItemResource->getTable('cataloginventory_stock_item'))
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['product_id', 'is_in_stock'])
            ->where('product_id IN (?)', $productIds);
        $data = $query->query()->fetchAll();
        foreach ($data as $row) {

            $stockStatusMapping[$row['product_id']] = $row['is_in_stock'];
        }
        return $stockStatusMapping;
    }
}
