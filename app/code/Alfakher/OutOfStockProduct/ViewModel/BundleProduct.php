<?php
namespace Alfakher\OutOfStockProduct\ViewModel;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;

class BundleProduct implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * [__construct]
     *
     * @param Product $product
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Product $product,
        StockRegistryInterface $stockRegistry
    ) {
        $this->product = $product;
        $this->stockRegistry = $stockRegistry;
    }
    /**
     * [getValidateProductForDropdown]
     *
     * @param mixed $selectionSku
     * @return mixed
     */
    public function getValidateProductForDropdown($selectionSku)
    {
        $productId = $this->product->getIdBySku($selectionSku);
        if ($productId) {
            try {
                $stockItem = $this->stockRegistry->getStockItem($productId);
                $isInStock = $stockItem ? $stockItem->getIsInStock() : false;
                if ($isInStock === true) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
            }
        }
    }
}
