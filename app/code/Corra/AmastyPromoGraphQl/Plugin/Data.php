<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Plugin;

use Amasty\Promo\Helper\Data as Subject;
use Magento\SalesRule\Model\RuleFactory;
use Amasty\Promo\Block\Items;
use Magento\Catalog\Model\ProductRepository;

class Data
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Items
     */
    private $promoItems;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    private $cartRuleData;

    private $cartSubtotal;

    /**
     * Data constructor.
     * @param RuleFactory $ruleFactory
     * @param Items $promoItems
     * @param ProductRepository $productRepository
     */
    public function __construct(
        RuleFactory       $ruleFactory,
        Items             $promoItems,
        ProductRepository $productRepository
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->promoItems = $promoItems;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetPromoItemsDataArray(Subject $subject, array $result)
    {
        $freeGiftData = [];
        $freeSamplesData = [];
        $triggeredProducts = $result['triggered_products'];

        foreach ($triggeredProducts as $key => $triggeredProduct) {
            $coupon = $this->ruleFactory->create()->load($key);
            $skuList = $triggeredProduct['sku'];
            $allowedMaxQty = $coupon->getData('discount_amount');
            if (isset($coupon) && $coupon->getCouponCode()) {
                $discountQty = $coupon->getData('discount_qty');
                if (0 == $discountQty || $discountQty == null) {
                    $stepAmountQty = $this->cartSubtotal / $coupon->getData('discount_step');
                    if ($stepAmountQty >= 2) {
                        $allowedMaxQty = $allowedMaxQty + $coupon->getData('discount_amount');
                    }
                }
                $freeGiftData = $this->getFreeData($skuList, $allowedMaxQty, $key);
            } else {
                $freeSamplesData = $this->getFreeData($skuList, $allowedMaxQty, $key);
            }
        }

        return [
            'common_qty' => $result['common_qty'],
            'triggered_products' => $triggeredProducts,
            'promo_sku' => $result['promo_sku'],
            'free_gifts' => $freeGiftData,
            'free_samples' => $freeSamplesData
        ];
    }

    /**
     * @param $sku
     * @return array
     */
    private function getProductDataBySku($sku)
    {
        $product = $this->productRepository->get($sku);
        $imageUrl = $this->promoItems->getImageHelper()
            ->init($product, 'small_image', ['type' => 'small_image'])
            ->getUrl();
        return [
            'name' => $product->getName(),
            'img' => $imageUrl
        ];
    }

    /**
     * @param $ruleData
     */
    public function setRuleData($ruleData)
    {
        $this->cartRuleData = $ruleData;
    }

    /**
     * @param $cartItems
     */
    public function setSubtotalValue($cartItems)
    {
        $subTotal = 0;
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->getParentItem()) {
                $subTotal += $cartItem->getBaseRowTotal() + $cartItem->getBaseTaxAmount();
            }
        }
        $this->cartSubtotal = $subTotal;
    }

    /**
     * @param $skuList
     * @param $allowedMaxQty
     * @param $key
     * @return array
     */
    public function getFreeData($skuList, $allowedMaxQty, $key)
    {
        $freeGiftData = [];
        foreach ($skuList as $sku => $skuItem) {
            $itemData = $this->getProductDataBySku($sku);
            $freeGiftData[] = [
                'sku' => $sku,
                'name' => $itemData['name'],
                'img' => $itemData['img'],
                'max_qty' => $allowedMaxQty,
                'rule_id' => $key,
                'is_added' => 0,
                'allowed_qty' => $allowedMaxQty
            ];
        }
        return $freeGiftData;
    }
}
