<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Plugin;

use Magento\Catalog\Helper\Data as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Data
{
    private const CATEGORY_URL_PREFIX_CONFIG = 'catalog/url/category_url_prefix';

    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface         $url,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->url = $url;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * After plugin to update category breadcrumb path
     *
     * @param Subject $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetBreadcrumbPath(
        Subject $subject,
        array   $result
    ): array {
        $prefix = $this->getCategoryPrefix();

        if (!empty($prefix)) {
            foreach ($result as $categoryKey => $breadCrumb) {
                if (!empty($breadCrumb['link'])) {
                    $baseUrl = $this->url->getBaseUrl();
                    $result[$categoryKey]['link'] =
                        $baseUrl . "$prefix/" . str_replace($baseUrl, "", $breadCrumb['link']);
                }
            }
        }

        return $result;
    }

    /**
     * Get prefix for Category URL from configuration
     *
     * @return null|string
     */
    private function getCategoryPrefix(): ?string
    {
        return $this->scopeConfig->getValue(self::CATEGORY_URL_PREFIX_CONFIG, ScopeInterface::SCOPE_STORE);
    }
}
