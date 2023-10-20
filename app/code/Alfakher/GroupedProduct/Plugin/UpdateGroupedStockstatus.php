<?php

declare(strict_types=1);

namespace Alfakher\GroupedProduct\Plugin;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\API\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Psr\Log\LoggerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class UpdateGroupedStockstatus
{
    /**
     * @var Grouped
     */
    private $groupedTypeInstance;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;
    public const B2B_WEBSITE_CODE = ['hookah_wholesalers','shisha_world_b2b','global_hookah'];
    /**
     * @var int|null
     */
    private $b2bWebsiteIds;
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     *
     * @param Grouped $groupedTypeInstance
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ResourceConnection $resource
     */
    public function __construct(
        Grouped                    $groupedTypeInstance,
        ProductRepositoryInterface $productRepository,
        LoggerInterface            $logger,
        WebsiteRepositoryInterface $websiteRepository,
        ResourceConnection         $resource
    ) {
        $this->groupedTypeInstance = $groupedTypeInstance;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->websiteRepository = $websiteRepository;
        $this->b2bWebsiteIds = $this->getWebsiteIdsArr();
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
    }

    /**
     * To update the stock status for hookah wolesalers grouped product only
     *
     * @param StockRegistry $subject
     * @param mixed $result
     * @param string $productSku
     * @param StockItemInterface $stockItem
     */
    public function afterUpdateStockItemBySku(
        StockRegistry $subject,
        $result,
        $productSku,
        StockItemInterface $stockItem
    ) {
        $toEnable = [];
        $productId = $this->resolveProductId($productSku);
        if ($productId && $stockItem->getIsInStock() && $stockItem->getQty() > 0) {
            $groupedProductsId = $this->groupedTypeInstance->getParentIdsByChild($productId);
            foreach ($groupedProductsId as $parentProductId) {
                $parentProduct = $this->getProduct($parentProductId);
                $associatedWebsites = ($parentProduct) ? $parentProduct->getWebsiteIds(): [];
                $commonWebsiteIdArr=array_intersect($this->b2bWebsiteIds, $associatedWebsites);
                if (!empty($commonWebsiteIdArr)) {
                    $toEnable[] = $parentProductId;
                }
            }
            if (!empty($toEnable)) {
                $this->updateParentStockStatuses($toEnable);
            }
        }
        return $result;
    }

    /**
     * Get Product id
     *
     * @param string $productSku
     * @return int|null
     * @throws NoSuchEntityException
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productRepository->get($productSku);
        if (!$product) {
            return null;
        }
        return $product->getId();
    }

    /**
     * Get Product
     *
     * @param int $parentProductId
     * @return ProductInterface|null
     */
    protected function getProduct($parentProductId)
    {
        $product = null;
        try {
            $product = $this->productRepository->getById($parentProductId);
        } catch (\Exception $exception) {
            $this->logger->error("could not fetch the grouped product: " . $exception->getMessage());
        }
        return $product;
    }

    /**
     * Update the stock status with grouped product parent id
     *
     * @param array $productIds
     * @return void
     */
    public function updateParentStockStatuses($productIds)
    {
        try {
            $stockItemTable = $this->resource->getTableName('cataloginventory_stock_item');
            $this->connection->update($stockItemTable, ['is_in_stock' => 1], $this->connection
                ->quoteInto('product_id in (?)', $productIds));
        } catch (Exception $exception) {
            $productIds = implode(",", $productIds);
            $this->logger->error("Grouped product stock not updated($productIds) : " . $exception->getMessage());
        }
    }

    /**
     * Get website id with code
     *
     * @param string $code
     * @return array
     * @throws NoSuchEntityException
     */
    public function getWebsiteIdsArr()
    {
        $b2bWebsiteIds = [];
        try {
            foreach (self::B2B_WEBSITE_CODE as $websiteCode) {
                $website = $this->websiteRepository->get($websiteCode);
                $websiteId = (int)$website->getId();
                $b2bWebsiteIds[] = $websiteId;
            }
        } catch (\Exception $exception) {
            $b2bWebsiteIds = [];
            $this->logger->error("Cannot fetch the b2b website id's : " . $exception->getMessage());
        }
        return $b2bWebsiteIds;
    }
}
