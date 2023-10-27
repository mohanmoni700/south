<?php

declare(strict_types=1);

namespace Alfakher\GroupedProduct\Model\Resolver\Product\Price;

use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\GroupedProductGraphQl\Model\Resolver\Product\Price\Provider;

/**
 * Provides product prices for configurable products
 */
class ProviderResolver extends Provider
{
    /**
     * Cache product prices so only fetch once
     *
     * @var AmountInterface[]
     */
    private $minimalProductAmounts;

    /**
     * Get minimal amount for cheapest product in group
     *
     * @param SaleableInterface $product
     * @param string $priceType
     * @return AmountInterface
     */
    private function getMinimalProductAmount(SaleableInterface $product, string $priceType): AmountInterface
    {
        if (empty($this->minimalProductAmounts[$product->getId()][$priceType])) {
            $products = $product->getTypeInstance()->getAssociatedProducts($product);
            $minPrice = null;
            foreach ($products as $item) {
                $item->setQty(PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $item->getPriceInfo()->getPrice($priceType);
                $priceValue = $price->getValue();
                if (($priceValue !== false) && ($priceValue <= ($minPrice === null ? $priceValue : $minPrice))) {
                    $minPrice = $price->getValue();
                    $this->minimalProductAmounts[$product->getId()][$priceType] = $price->getAmount();
                }
            }
        }
        $amount = isset($this->minimalProductAmounts[$product->getId()][$priceType]) ?
            $this->minimalProductAmounts[$product->getId()][$priceType] : [];
        return $amount;
    }
}
