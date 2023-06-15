<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\ViewModel;

/**
 * View model class
 *
 * @author af_bv_op
 */

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Item;

class GrossMargin implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const MODULE_ENABLE = "hookahshisha/gross_margin_group/gross_margin_enable";

    /**
     * @var StockRegistryInterface
     */
    private StockRegistryInterface $stockRegistry;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $stockRegistry

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Check if module is enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isModuleEnabled($storeId)
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $storeId);
    }

    /**
     * Validate gross margin
     *
     * @param Item $item
     * @return mixed
     */
    public function validateGrossMargin($item)
    {
        if ($item->getGrossMargin() <= 0) {
            $cost = 0;
            if ($item->getProduct()) {
                $cost = $item->getProduct()->getCost();
            }
            $price = $item->getPrice();
            try {
                $grossMargin = ($price - $cost) / $price * 100;
                return number_format($grossMargin, 2, ".", "");
            } catch (\Exception $e) {
                return 0.00;
            }
        }
        return $item->getGrossMargin();
    }

    /**
     * Return available balance quantity
     *
     * @param int $productId
     * @return float
     */
    public function getQty(int $productId): float
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        if (!empty($stockItem->getQty())) {
            return $stockItem->getQty();
        } else {
            return 0;
        }
    }
}
