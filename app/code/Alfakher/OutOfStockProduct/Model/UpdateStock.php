<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * UpdateStock Data
 */
class UpdateStock extends Command
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param ResourceConnection $resource
     * @param CollectionFactory $productCollectionFactory
     * @param State $state
     */
    public function __construct(
        ResourceConnection $resource,
        CollectionFactory  $productCollectionFactory,
        State              $state
    ) {
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('product_stock:refresh')
            ->setDescription('Refresh the Product stock');
    }

    /**
     * update Grouped product stock status
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        try {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToFilter('status', ['eq' => 1]);
            $collection->addAttributeToFilter('type_id', array('in' => ['grouped', 'configurable']));
            $collection->addAttributeToSelect('*');
            $size = $collection->getSize();
            foreach ($collection as $product) {
                /** If parent product is set as OOS manually then ignore the product */
                if (empty($this->getParentProductStockStatus($product->getEntityId()))) {
                    continue;
                }
                $children = $this->getChildProducts($product->getEntityId());
                $childProductId = [];
                foreach ($children as $child) {
                    $childProductId[] = $child['child_id'];
                }
                $stockData = $this->getStockItem($childProductId);
                if (!empty($stockData) && (count($childProductId) == count($stockData))) {
                    // Set main product stock status to 0
                    $this->updateStockStatus($product->getEntityId(), 0);
                    $output->writeln($product->getEntityId() . ' successfully updated: as OOS');
                } else {
                    // Set main product stock status to 1
                    $this->updateStockStatus($product->getEntityId(), 1);
                    $output->writeln($product->getEntityId() . ' successfully updated as In-Stock');
                }
            }
            $output->writeln($size . ' Products successfully updated');
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }
    }

    /**
     * get ChildProducts Stock details
     * @param $productIds
     * @return array
     */
    public function getStockItem($productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $productIds = implode(",", $productIds);
        $query = "SELECT product_id , qty, is_in_stock  FROM " .
            $this->resource->getTableName('cataloginventory_stock_item') . " WHERE " .
            "(qty <= 0 OR is_in_stock = 0) AND product_id IN (" . $productIds . ")";
        return $this->resource->getConnection()->fetchAll($query);
    }

    /**
     * Get the product status
     * @param $productId
     * @return array|false
     */
    public function getParentProductStockStatus($productId)
    {
        if (empty($productId)) {
            return false;
        }
        $query = "SELECT is_in_stock FROM " . $this->resource->getTableName('cataloginventory_stock_item') .
            " WHERE product_id = " . $productId . " AND is_in_stock = 1";
        return $this->resource->getConnection()->fetchAll($query);
    }

    /**
     * update product Stock Status
     * @param $productId
     * @param $value
     * @return void
     */
    public function updateStockStatus($productId, $value)
    {
        $updateQuery = "UPDATE " . $this->resource->getTableName('cataloginventory_stock_item') .
            " SET is_in_stock = " . $value .
            " WHERE product_id = " . $productId;
        $this->resource->getConnection()->query($updateQuery);
    }

    /**
     * get ChildProducts ID
     * @param $productId
     * @return array
     */
    public function getChildProducts($productId)
    {
        $query = "select child_id FROM " . $this->resource->getTableName('catalog_product_relation') .
            " WHERE parent_id = " . $productId;
        return $this->resource->getConnection()->fetchAll($query);
    }
}

