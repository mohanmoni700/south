<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Block\Product\View\Type;

use Alfakher\Productpageb2b\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped as MagentoGrouped;

class Grouped extends MagentoGrouped
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context    $context,
        ArrayUtils $arrayUtils,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Data       $helperData,
        array      $data = []
    ) {
        parent::__construct($context, $arrayUtils, $productRepository, $data);
        $this->helperData = $helperData;
    }

    /**
     * Get sorted products
     *
     * @return array
     */
    public function getSortedProducts(): array
    {
        $associatedProducts = $this->getAssociatedProducts();
        $inStockProducts = [];
        $outOfStockProducts = [];

        foreach ($associatedProducts as $_item) {
            $productAvailableQty = $this->helperData->getStockQty($_item->getId());
            if ($productAvailableQty > 0) {
                $inStockProducts[] = $_item;
            } else {
                $outOfStockProducts[] = $_item;
            }
        }

        usort($inStockProducts, function ($a, $b) {
            return strcasecmp($a->getName(), $b->getName());
        });
        usort($outOfStockProducts, function ($a, $b) {
            return strcasecmp($a->getName(), $b->getName());
        });

        return array_merge($inStockProducts, $outOfStockProducts);
    }
}
