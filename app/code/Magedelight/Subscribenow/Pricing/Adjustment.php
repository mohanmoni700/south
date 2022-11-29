<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Pricing;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Source\DiscountType;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;
use Magento\GroupedProduct\Model\Product\Type\GroupedFactory;

class Adjustment implements AdjustmentInterface
{
    /**
     * Adjustment code tax
     */
    const ADJUSTMENT_CODE = 'subscription_discount';

    private $configurable;
    private $productRepository;
    private $parentProduct;
    private $groupedFactory;
    private $grouped;
    private $registry;
    private $helper;
    protected $sortOrder;
    private $groupParentProduct = null;

    public function __construct(
        Configurable $configurable,
        ProductRepositoryInterface $productRepository,
        GroupedFactory $groupedFactory,
        Registry $registry,
        Data $helper,
        $sortOrder = null
    ) {
        $this->sortOrder = $sortOrder;
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->groupedFactory = $groupedFactory;
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * Get adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    /**
     * Define if adjustment is included in base price
     *
     * @return bool
     */
    public function isIncludedInBasePrice()
    {
        return true;
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     */
    public function isIncludedInDisplayPrice()
    {
        return true;
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        return 0;
    }

    /**
     * Apply adjustment amount and return result value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        if ($this->canApplyAdjustment($saleableItem)) {
            $discountAmount = $this->parentProduct->getDiscountAmount();
            if ($this->parentProduct->getDiscountType() == DiscountType::PERCENTAGE) {
                $percentageAmount = $amount * ($discountAmount / 100);
                $amount = $amount - $percentageAmount;
            } else {
                $amount = $amount - $discountAmount;
            }
        }
        return $amount;
    }

    private function canApplyAdjustment($product)
    {
        $isEnable = $this->helper->isModuleEnable();

        if ($isEnable && $currentProduct = $this->registry->registry('current_product')) {
            $parentConfigObject = null;
            if ($currentProduct->getTypeId() == 'configurable') {
                $parentConfigObject = $this->configurable->getParentIdsByChild($product->getId());
            }

            if ($parentConfigObject) {
                return $this->getParentProduct($parentConfigObject);
            } elseif ($this->isValidSubscription($product)
                && $currentProduct->getTypeId() == 'grouped'
            ) {
                return $this->getParentGroupProduct($product);
            }
        }

        return false;
    }

    public function isValidSubscription($product)
    {
        $isSubscription = $product->getIsSubscription();
        $subscriptionType = $product->getSubscriptionType();
        if ($isSubscription && $subscriptionType == PurchaseOption::EITHER) {
            return true;
        }

        return false;
    }

    private function getParentGroupProduct($product)
    {
        if ($product->getTypeId() == 'grouped') {
            $this->groupParentProduct = $product;
        }

        $parentIds = $this->getGroupParentId($product->getId());
        if ($this->groupParentProduct && !empty($parentIds)) {
            if (in_array($this->groupParentProduct->getId(), $parentIds)) {
                $this->parentProduct = $this->groupParentProduct;
                $isSubscription = $this->parentProduct->getIsSubscription();
                $subscriptionType = $this->parentProduct->getSubscriptionType();
                if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getParentProduct($parentConfigObject)
    {
        $id = $parentConfigObject[0];
        $parentProduct = $this->productRepository->getById($id);
        if ($parentProduct->getTypeId() == 'configurable') {
            $this->parentProduct = $parentProduct;
            $isSubscription = $parentProduct->getIsSubscription();
            $subscriptionType = $parentProduct->getSubscriptionType();
            if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if adjustment should be excluded from calculations along with the given adjustment
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function isExcludedWith($adjustmentCode)
    {
        return $this->getAdjustmentCode() === $adjustmentCode;
    }

    /**
     * Return sort order position
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    private function getGroupParentId($childId)
    {
        if (!$this->grouped) {
            $this->grouped = $this->groupedFactory->create();
        }
        return $this->grouped->getParentIdsByChild($childId);
    }
}
