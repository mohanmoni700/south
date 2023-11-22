<?php

declare(strict_types=1);

namespace Alfakher\GiftProduct\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

class Provider implements ProviderInterface
{
    /**
     * Get Gift-card product minimal final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getProductAmount($product, FinalPrice::PRICE_CODE);
    }

    /**
     * Get Gift-card product minimal regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface // NOSONAR
    {
        return $this->getProductAmount($product, FinalPrice::PRICE_CODE);
    }

    /**
     * Get Gift-card product maximal final price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getProductAmount($product, 'maximal_final_price');
    }

    /**
     * Get Gift-card product minimal regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface // NOSONAR
    {
        return $this->getProductAmount($product, 'maximal_final_price');
    }

    /**
     * Get Gift-card product regular price
     *
     * @param SaleableInterface $product
     * @return AmountInterface
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getProductAmount($product, RegularPrice::PRICE_CODE);
    }

    /**
     * Get gift-card product amount by price type
     *
     * @param SaleableInterface $product
     * @param string            $priceType
     *
     * @return AmountInterface
     */
    public function getProductAmount(SaleableInterface $product, string $priceType): AmountInterface
    {
        return $product->getPriceInfo()->getPrice($priceType)->getAmount();
    }
}
