<?php
declare(strict_types=1);
namespace Alfakher\SeoUrlPrefix\Model\CatalogUrlRewrite;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator as ProductPathGenerator;
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

    // CHANGE THESE FOR CUSTOM STATIC PREFIX ROUTE of PRODUCT and PRODUCT CATEGORY
    public const PRODUCT_PREFIX_ROUTE = 'p';

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
     * @return string
     */
    public function getUrlPath($product, $category = null)
    {
        $storeDetails = $this->scopeConfig->getValue(self::PREFIX_STORES);
        $storeIds = $storeDetails ? explode(',', $storeDetails) : [];
        
        $storeid ="";
        $storeManagerDataList = $this->storeManager->getStores();
        foreach ($storeManagerDataList as $key => $value) {
            if (in_array($key, $storeIds)) {
                $storeid = $key;
            }
        }
        $productwebsite = $product->getWebsiteIds();

        foreach ($productwebsite as $value) {
            if ($product->getTypeId() == 'grouped') {
                $prifix = 'p/';
            } else {
                $prifix = '';
            }
        }

        $path = $product->getData('url_path');
        if ($path === null) {
            $path = $product->getUrlKey()
            ? $this->prepareProductUrlKey($product)
            : $this->prepareProductDefaultUrlKey($product);
        }

        if ($product->getTypeId() == 'grouped' && strpos($path, 'p/') === false) {
            $path = $prifix . $path;
        }

        return $category === null
        ? $path
        : $this->categoryUrlPathGenerator->getUrlPath($category) . '/' . $path;
    }
}
