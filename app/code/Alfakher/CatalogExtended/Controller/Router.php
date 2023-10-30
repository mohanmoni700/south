<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements RouterInterface
{
    private const CATALOG_URL_EXCLUDE_CONFIG = 'catalog/url/exclude';

    private const CATALOG_PRODUCT_URL_PATH = 'catalog/product/view';

    /**
     * @var ActionFactory
     */
    private $actionFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ActionFactory $actionFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ActionFactory        $actionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request)
    {
        $matched = false;
        $pathParts = explode('/', trim($request->getPathInfo(), '/'));
        $textToMatch = $this->scopeConfig->getValue(self::CATALOG_URL_EXCLUDE_CONFIG, ScopeInterface::SCOPE_STORE);

        if (count($pathParts) <= 1 ||
            empty($textToMatch) ||
            str_contains($request->getPathInfo(), self::CATALOG_PRODUCT_URL_PATH)
        ) {
            return null;
        }

        $textToMatch = explode("\n", str_replace("\r", "", $textToMatch));

        foreach ($pathParts as $key => $value) {
            if (in_array($value, $textToMatch)) {
                $matched = true;
                unset($pathParts[$key]);
            }
        }

        if ($matched) {
            $processedPath = implode('/', $pathParts);
            $request->setPathInfo($processedPath);

            return $this->actionFactory->create(Forward::class);
        }

        return null;
    }
}
