<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Cron;

/** Cron to auto apply the category product position */

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\VisualMerchandiser\Model\Rules;
use Magento\VisualMerchandiser\Model\Sorting;
use Psr\Log\LoggerInterface;

class SortCategoryProduct
{
    /** @var CollectionFactory */
    protected $categoryCollection;
    /** @var ProductCollectionFactory */
    protected $productCollectionFactory;
    /** @var Sorting */
    protected $sorting;
    /** @var LoggerInterface */
    protected $logger;
    /** @var Rules */
    protected $rules;

    /**
     * @param CollectionFactory $categoryCollection
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Rules $rules
     * @param Sorting $sorting
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory        $categoryCollection,
        ProductCollectionFactory $productCollectionFactory,
        Rules                    $rules,
        Sorting                  $sorting,
        LoggerInterface          $logger
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->rules = $rules;
        $this->sorting = $sorting;
        $this->logger = $logger;
    }

    public function execute()
    {
        $categoryCollection = $this->getCategoryCollection();
        foreach ($categoryCollection as $category) {
            if ($this->isSmartCategoryEnabled($category)) {
                $collection = $this->productCollectionFactory->create();
                $collection->addAttributeToSelect('*');
                $collection->addCategoryFilter($category);
                try {
                    $sortedCollection = $this->sorting->applySorting($category, $collection);
                    $positions = [];
                    $idx = 0;
                    foreach ($sortedCollection as $item) {
                        /* @var $item ProductInterface */
                        $positions[$item->getId()] = $idx;
                        $idx++;
                    }
                    $category->setPostedProducts($positions);
                    $category->save();
                    $this->logger->debug('Category Sort CRON Executed: ');
                } catch (\Exception $e) {
                    $this->logger->debug('Category Sort CRON Error: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get all the categories
     * @return array|Collection
     */
    public function getCategoryCollection()
    {
        try {
            return $this->categoryCollection->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', '1');
        } catch (\Exception $e) {
            $this->logger->debug('Category Sort CRON Error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Check is category is enabled for smart category
     * @param $category
     * @return bool
     */
    public function isSmartCategoryEnabled($category): bool
    {
        if ($category) {
            $rules = $this->rules->loadByCategory($category);
            return (bool)$rules->getIsActive();
        }
        return false;
    }
}
