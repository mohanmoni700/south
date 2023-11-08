<?php
declare(strict_types=1);
namespace Alfakher\SeoUrlPrefix\Model\CatalogUrlRewrite;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator as CategoryPathGenerator ;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class for generation category url_path
 */
class CategoryUrlPathGenerator extends CategoryPathGenerator
{
    /**
     * Prefix stores
     */
    public const PREFIX_STORES = 'hookahshisha/prefix_add_seo/seo_stores';

    /**
     * Minimal category level that can be considered for generate path
     */
    public const MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING = 3;

    /**
     * XML path for category url suffix
     */
    public const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * @var array
     */
    public array $categoryPathPrefix = [
        'hookah_wholesalers_store_view' => 'c/',
        'global_hookah_store_view' => 'collections/'
    ];

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $categoryUrlSuffix = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Build category URL path
     *
     * @param CategoryInterface|AbstractModel $category
     * @param null|CategoryInterface|AbstractModel $parentCategory
     * @return string
     * @throws NoSuchEntityException
     */
    public function getUrlPath($category, $parentCategory = null)
    {
        $rootId = "";
        $storeDetails = $this->scopeConfig->getValue(self::PREFIX_STORES);
        $storeIds = $storeDetails ? explode(',', $storeDetails) : [];

        $storeid ="";
        $storeCode = '';
        $storeManagerDataList = $this->storeManager->getStores();
        foreach ($storeManagerDataList as $key => $value) {
            if (in_array($key, $storeIds) && $key == $category->getStoreId()) {
                $storeid = $key;
                $storeCode = $value->getCode();
                $rootId = $this->storeManager->getStore($storeid)->getRootCategoryId();
            }
        }

        $All_Id = [];
        $All_Id = $category->getParentIds();

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

            $first = strtok($parentPath, '/');
            if ($first == 'c' || $first == 'collections') {
                $string_path = str_split($parentPath);
                array_splice($string_path, 0, (strlen($first) + 1));
                $new_path = implode("", $string_path);
                $path = $new_path === '' ? $path : $new_path . '/' . $path;

            } else {
                $path = $parentPath === '' ? $path : $parentPath . '/' . $path;
            }
        }


        if (in_array($rootId, $All_Id)) {
            $prefix = $this->categoryPathPrefix[$storeCode] ?? '';
            return $prefix . $path;
        } else {
            return $path;
        }
    }

    /**
     * Define whether we should generate URL path for parent
     *
     * @param Category $category
     * @return bool
     */
    protected function isNeedToGenerateUrlPathForParent($category)
    {
        return $category->isObjectNew() || $category->getLevel() >= self::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
    }
}
