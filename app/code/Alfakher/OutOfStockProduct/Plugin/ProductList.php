<?php

namespace Alfakher\OutOfStockProduct\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProductList
{
    /**
     * @var $scopeConfig
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * After plugin to sort OOS products to last
     *
     * @param ListProduct $subject
     * @param $result
     * @return mixed
     */
    public function afterGetLoadedProductCollection(ListProduct $subject, $result)
    {
        // Check if the feature is enabled
        $isEnabled = $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock_products_last');
        if ($isEnabled) {
            // Modify the collection to move out-of-stock products to the bottom
            $result->getSelect()->order('is_in_stock DESC');
        }
        return $result;
    }
}
