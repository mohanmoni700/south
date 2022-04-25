<?php

namespace Alfakher\GrossMargin\Model\Magento\VisualMerchandiser\Category;

/**
 * @author af_bv_op
 */
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\Category\Product\PositionResolver;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\VisualMerchandiser\Model\Position\Cache;
use Magento\VisualMerchandiser\Model\Resolver\QuantityAndStock;
use Magento\VisualMerchandiser\Model\Sorting;
use Zend_Db_Select_Exception;

class Products extends \Magento\VisualMerchandiser\Model\Category\Products
{

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var Cache
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheKey;

    /**
     * @var Sorting
     */
    protected $sorting;

    /**
     * @var PositionResolver
     */
    private $positionResolver;

    /**
     * @var QuantityAndStock
     */
    private $quantityStockResolver;

    /**
     * @var array|bool
     */
    private $positions = false;

    /**
     * @param ProductFactory $productFactory
     * @param Manager $moduleManager
     * @param Cache $cache
     * @param Sorting $sorting
     * @param QuantityAndStock $quantityStockResolver
     * @param PositionResolver|null $positionResolver
     */
    public function __construct(
        ProductFactory $productFactory,
        Manager $moduleManager,
        Cache $cache,
        Sorting $sorting,
        QuantityAndStock $quantityStockResolver,
        PositionResolver $positionResolver = null
    ) {
        $this->_productFactory = $productFactory;
        $this->_moduleManager = $moduleManager;
        $this->_cache = $cache;
        $this->sorting = $sorting;
        $this->quantityStockResolver = $quantityStockResolver;
        $this->positionResolver = $positionResolver ?: ObjectManager::getInstance()->get(PositionResolver::class);

        parent::__construct($productFactory, $moduleManager, $cache, $sorting, $quantityStockResolver, $positionResolver);
    }

    /**
     * Applies position information
     *
     * @param Collection $collection
     * @param int $categoryId
     * @param array|null $productPositions (Optional)
     * @return Collection
     * @throws LocalizedException
     * @throws \Zend_Json_Exception
     */
    private function applyPositions(Collection $collection, int $categoryId, $productPositions = null)
    {
        if (!$this->_cache->getPositions($this->_cacheKey)) {
            if (!is_array($productPositions)) {
                $collection->getSelect()->where('at_position.category_id = ?', $categoryId);
                $collection->joinField(
                    'position',
                    'catalog_category_product',
                    'position',
                    'product_id=entity_id',
                    null,
                    'left'
                );
                $collection->setOrder('position', $collection::SORT_ORDER_ASC);
                $productPositions = $this->positionResolver->getPositions($categoryId);
                $collection->setOrder('entity_id', $collection::SORT_ORDER_DESC);
            }

            $this->positions = $productPositions;
        } else {
            $collection->getSelect()->reset(Select::WHERE)->reset(Select::HAVING);
            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($this->getPositions())]);

            return $this->applyCachedChanges($collection);
        }

        return $collection;
    }

    /**
     * Add needed column to the Select on the first position
     *
     * There are no problems for MySQL with several same columns in the result set
     *
     * @param Select $select
     * @param string $columnName
     * @return void
     * @throws Zend_Db_Select_Exception
     */
    private function prependColumn(Select $select, string $columnName)
    {
        $columns = $select->getPart(Select::COLUMNS);
        array_unshift($columns, ['e', $columnName, null]);
        $select->setPart(Select::COLUMNS, $columns);
    }

    /**
     * Retrieves positions
     *
     * @return array|bool
     * @throws \Zend_Json_Exception
     */
    private function getPositions()
    {
        $positions = $this->_cache->getPositions($this->_cacheKey);
        return is_array($positions) ? $positions : $this->positions;
    }

    /**
     * Builds the collection for a grid
     *
     * @param int $categoryId
     * @param int $store (Optional)
     * @param array|null $productPositions (Optional)
     * @return Collection
     * @throws LocalizedException
     * @throws \Zend_Json_Exception
     */
    public function getCollectionForGrid($categoryId, $store = null, $productPositions = null)
    {
        /** @var Collection $collection */
        $collection = $this->getFactory()->create()->getCollection()
            ->addAttributeToSelect(
                [
                    'sku',
                    'name',
                    'price',
                    'small_image',
                ]
            );

        /*af_bv_op; start*/
        $collection->addAttributeToSort('type_id', 'asc');
        /*af_bv_op; end*/

        if (is_array($productPositions)) {
            $productIds = array_keys($productPositions);
            $collection->getSelect()->distinct()->where('e.entity_id IN(?)', $productIds);
        }

        $collection = $this->quantityStockResolver->joinStock($collection);
        $collection = $this->applyPositions($collection, $categoryId, $productPositions);

        if ($store !== null) {
            $collection->addStoreFilter($store);
        }

        return $collection;
    }
}
