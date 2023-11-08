<?php
declare(strict_types=1);
namespace Alfakher\SeoUrlPrefix\Model\CatalogUrlRewrite;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator as ProductPathGenerator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

class ProductUrlPathGenerator extends ProductPathGenerator
{
    /**
     * Prefix stores
     */
    public const PREFIX_STORES = 'hookahshisha/prefix_add_seo/seo_stores';

    /**
     * @var array
     */
    public array $productPathPrefix = [
        'hookah_wholesalers_store_view' => 'p/',
        'global_hookah_store_view' => 'product/'
    ];

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param Product $product
     * @param Category $category
     * @param int $storeId
     * @return string
     */
    public function getUrlPath($product, $category = null, $storeId = null)
    {
        $storeDetails = $this->scopeConfig->getValue(self::PREFIX_STORES);
        $storeIds = $storeDetails ? explode(',', $storeDetails) : [];

        $path = $product->getData('url_path');

        try {
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
        } catch (NoSuchEntityException $e) {
            return parent::getUrlPath($product, $category, $storeId);
        }

        $prefix = in_array($storeId, $storeIds) ? $this->productPathPrefix[$storeCode] ?? '' : '';

        if ($path === null) {
            $path = $product->getUrlKey()
                ? $this->prepareProductUrlKey($product)
                : $this->prepareProductDefaultUrlKey($product);
        }
        $path = $prefix . $path;

        return $category === null
            ? $path
            : $this->categoryUrlPathGenerator->getUrlPath($category) . '/' . $path;
    }

    /**
     * Retrieve Product Url path with suffix
     *
     * @param Product $product
     * @param int $storeId
     * @param Category $category
     * @return string
     */
    public function getUrlPathWithSuffix($product, $storeId, $category = null)
    {
        return $this->getUrlPath($product, $category, $storeId) . $this->getProductUrlSuffix($storeId);
    }
}
