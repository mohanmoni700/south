<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\VisualMerchandiser\Model\Sorting as MagentoSorting;
use Magento\VisualMerchandiser\Model\Resolver\QuantityAndStock;
use Magento\VisualMerchandiser\Model\Sorting\Factory;

/** overwrite class to Adding quantityStock join for OutStockBottom Sorting as well */
class OutStockBottomSorting extends MagentoSorting
{
    /**
     * @var QuantityAndStock
     */
    private $quantityStockResolver;

    /**
     * @param QuantityAndStock $quantityAndStockResolver
     * @throws LocalizedException
     */
    public function __construct(Factory $factory, QuantityAndStock $quantityAndStockResolver)
    {
        parent::__construct($factory, $quantityAndStockResolver);
        $this->quantityStockResolver = $quantityAndStockResolver;
    }
    /**
     * Apply sorting to collection
     * Adding quantityStock join for OutStockBottom Sorting as well
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws LocalizedException
     */
    public function applySorting(
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $sortBuilder = $this->getSortingInstance($category->getAutomaticSorting());
        if ($category->getAutomaticSorting() &&
            ($this->sortClasses[$category->getAutomaticSorting()] === 'LowStockTop') ||
            ($this->sortClasses[$category->getAutomaticSorting()] === 'OutStockBottom')) {
            $collection = $this->quantityStockResolver->joinStock($collection);
            $collection->getSelect()->group('e.entity_id');
        }
        $_collection = $sortBuilder->sort($collection);

        // We need the collection to be clear for it to take effect after sorting is applied
        if ($_collection->isLoaded()) {
            $_collection->clear();
        }

        return $_collection;
    }
}
