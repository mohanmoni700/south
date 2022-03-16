<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Alfakher\SeoUrlPrefix\Model\CatalogUrlRewrite;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;

/**
 * Class for generation category url_path
 */
class CategoryUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
{
    /**
     * Minimal category level that can be considered for generate path
     */
    public const MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING = 3;

    /**
     * XML path for category url suffix
     */
    public const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $categoryUrlSuffix = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Build category URL path
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface|\Magento\Framework\Model\AbstractModel $category
     * @param null|\Magento\Catalog\Api\Data\CategoryInterface|\Magento\Framework\Model\AbstractModel $parentCategory
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlPath($category, $parentCategory = null)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/caturlstore.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $storeManagerDataList = $this->storeManager->getStores();
        foreach ($storeManagerDataList as $key => $value) {
            $storeid = $key;
            if ($value['code'] == "hookah_wholesalers_store_view") {
                $storeid = $key;
                $rootId = $this->storeManager->getStore($storeid)->getRootCategoryId();
            }
        }
        // $logger->info(print_r($category->getData()));
        // $logger->info(print_r($category->getParentIds()));

        $All_Id = [];
        $All_Id = $category->getParentIds();

        // foreach ($category->getParentIds() as $key => $value) {
        //     $logger->info($value);
        // }

        // $parentCategories = $category->getParentCategories();
        // foreach ($parentCategories as $cat) {
        //     // echo ;
        //     $logger->info($cat->getId());
        // }

        $storeId = $category->getStoreId();
        $prifix = $storeId == $storeid ? 'c/' : '';

        if (in_array($category->getParentId(), [Category::ROOT_CATEGORY_ID, Category::TREE_ROOT_ID])) {
            return '';
        }
        $path = $category->getUrlPath();
        if ($path !== null && !$category->dataHasChangedFor('url_key') && !$category->dataHasChangedFor('parent_id')) {
            return $path;
        }
        $path = $category->getUrlKey();
        if ($path === false) {
            return $category->getUrlPath();
        }
        if ($this->isNeedToGenerateUrlPathForParent($category)) {
            $parentCategory = $parentCategory === null ?
            $this->categoryRepository->get($category->getParentId(), $category->getStoreId()) : $parentCategory;
            $parentPath = $this->getUrlPath($parentCategory);

            $logger->info($parentPath);

            $first = strtok($parentPath, '/');
            if ($first == 'c') {
                $string_path = str_split($parentPath);
                array_splice($string_path, 0, 2);
                $new_path = implode("", $string_path);
                $path = $new_path === '' ? $path : $new_path . '/' . $path;

            } else {
                $path = $parentPath === '' ? $path : $parentPath . '/' . $path;
            }
        }

        if (in_array($rootId, $All_Id)) {
            return 'c/' . $path;
        } else {
            return $path;
        }
    }

    /**
     * Define whether we should generate URL path for parent
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return bool
     */
    protected function isNeedToGenerateUrlPathForParent($category)
    {
        return $category->isObjectNew() || $category->getLevel() >= self::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
    }
}
