<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Plugin\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as Subject;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlModifierPlugin extends Subject
{
    private const PRODUCT_URL_PREFIX_CONFIG = 'catalog/url/product_url_prefix';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param UrlFactory $urlFactory
     * @param StoreManagerInterface $storeManager
     * @param FilterManager $filter
     * @param SidResolverInterface $sidResolver
     * @param UrlFinderInterface $urlFinder
     * @param array $data
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlFactory            $urlFactory,
        StoreManagerInterface $storeManager,
        FilterManager         $filter,
        SidResolverInterface  $sidResolver,
        UrlFinderInterface    $urlFinder,
        ScopeConfigInterface  $scopeConfig,
        array                 $data = []
    ) {
        parent::__construct($urlFactory, $storeManager, $filter, $sidResolver, $urlFinder, $data, $scopeConfig);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Around plugin to modify product url key
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param Product $product
     * @param array $params
     * @return string
     */
    public function aroundGetUrl(
        Subject  $subject,
        callable $proceed,
        Product  $product,
                 $params = []
    ): string {
        $routePath = '';
        $routeParams = $params;
        $storeId = $product->getStoreId();
        $categoryId = null;
        $textToAppend = $this->scopeConfig->getValue(self::PRODUCT_URL_PREFIX_CONFIG, ScopeInterface::SCOPE_STORE);

        if (empty($textToAppend)) {
            return $proceed($product, $params);
        }

        list($categoryId, $requestPath, $routeParams) = $this->processRequestPath(
            $params,
            $product,
            $categoryId,
            $routeParams,
            $storeId
        );

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $textToAppend . '/' . $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        $url = $this->urlFactory->create()->setScope($storeId);
        return $url->getUrl($routePath, $routeParams);
    }

    /**
     * Process Product URL request path
     *
     * @param array $params
     * @param Product $product
     * @param string|null $categoryId
     * @param array $routeParams
     * @param int $storeId
     * @return array
     */
    private function processRequestPath(
        array   $params,
        Product $product,
        ?string $categoryId,
        array   $routeParams,
        int     $storeId
    ): array {
        if (!isset($params['_ignore_category']) && $product->getCategoryId() && !$product->getDoNotUseCategoryId()) {
            $categoryId = $product->getCategoryId();
        }

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $filterData = [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeId,
                ];
                $useCategories = $this->scopeConfig->getValue(
                    \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
                    ScopeInterface::SCOPE_STORE
                );

                $filterData[UrlRewrite::METADATA]['category_id']
                    = $categoryId && $useCategories ? $categoryId : '';

                $rewrite = $this->urlFinder->findOneByData($filterData);

                if ($rewrite) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            }
        }

        return [$categoryId, $requestPath, $routeParams];
    }
}
