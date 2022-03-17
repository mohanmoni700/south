<?php
namespace Alfakher\SeoUrlPrefix\Model\CatalogUrlRewrite;

class ProductUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
{

    // CHANGE THESE FOR CUSTOM STATIC PREFIX ROUTE of PRODUCT and PRODUCT CATEGORY
    public const PRODUCT_PREFIX_ROUTE = 'p';

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     */
    public function getUrlPath($product, $category = null)
    {

        $storeManagerDataList = $this->storeManager->getStores();

        foreach ($storeManagerDataList as $key => $value) {
            if ($value['code'] == "hookah_wholesalers_store_view") {
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
