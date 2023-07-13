<?php
/**
 * Copyright (c) 2020 Magedelight Solution Pvt. Ltd.
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GPL 3.0
 * @link     http://www.magedelight.com/
 */

namespace Magedelight\Subscribenow\Pricing\Price;

use Magedelight\Subscribenow\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magedelight\Subscribenow\Model\Service\DiscountService;

/**
 * Class SubscriptionDiscountPrice
 *
 * Product Listing & Product Detail Page pricing
 * Discount for subscription only product
 *
 * @since 200.5.0
 * @package Magedelight\Subscribenow\Pricing\Price
 */
class SubscriptionDiscountPrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'subscription_discount_price';

    protected $discountService;
    protected $helper;
    protected $productFactory;
    protected $linkFactory;

    private $parentProduct;

    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        PriceCurrencyInterface $priceCurrency,
        Data $helper,
        DiscountService $discountService,
        ProductFactory $productFactory,
        LinkFactory $linkFactory
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->discountService = $discountService;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (!$this->helper->isModuleEnable()) {
            return false;
        }

        if ($product = $this->isSubscription($this->product)) {
            $finalPrice = $this->priceInfo->getPrice('final_price')->getValue();
            $this->value = $this->discountService->calculateDiscount($finalPrice, $product);
        } else {
            $this->value = false;
        }

        return $this->value;
    }

    public function isSubscription($product)
    {
        if ($parentProductId = $product->getData('parent_product_id')) {
            $product = $this->getCurrentProduct($parentProductId);
        }

        if ($linkId = $product->getData('link_id')) {
            $product = $this->getGroupProduct($linkId);
        }

        if ($product) {
            return $this->discountService->isSubscriptionOnly($product);
        }

        return false;
    }

    public function getGroupProduct($linkId)
    {
        $groupProduct = false;
        $linkProduct = $this->linkFactory->create()->load($linkId);
        if ($linkProduct && $linkProduct->getLinkTypeId() == 3) {
            $groupProduct = $this->getCurrentProduct($linkProduct->getProductId());
        }

        return $groupProduct;
    }

    public function getCurrentProduct($id)
    {
        if (!$this->parentProduct || $this->parentProduct->getId() !== $id) {
            $this->parentProduct = $this->productFactory->create()->load($id);
        }
        return $this->parentProduct;
    }
}
