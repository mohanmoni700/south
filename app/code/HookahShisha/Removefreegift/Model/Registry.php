<?php

namespace HookahShisha\Removefreegift\Model;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Promo\Helper\Item;
use Amasty\Promo\Helper\Messages;
use Magento\Store\Model\Store;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\ItemRegistry\PromoItemRegistry;
use Magento\Framework\App\Request\Http;

/**
 * Promo Items Registry
 */
class Registry extends \Amasty\Promo\Model\Registry
{
    /**
     * Product types available for auto add to cart
     */
    public const AUTO_ADD_PRODUCT_TYPES = ['simple', 'virtual', 'downloadable', 'bundle'];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var array
     */
    protected $fullDiscountItems;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    protected $discountCalculator;

    /**
     * @var ItemRegistry\PromoItemRegistry
     */
    protected $promoItemRegistry;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httprequest

    /**
     * construct method
     * 
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param ProductCollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param \Amasty\Promo\Helper\Messages $promoMessagesHelper
     * @param \Magento\Store\Model\Store $store
     * @param \Amasty\Promo\Model\DiscountCalculator $discountCalculator
     * @param \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry $promoItemRegistry
     * @param \Magento\Framework\App\Request\Http $httprequest
     */
    public function __construct(
        Session $resourceSession,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Item $promoItemHelper,
        Messages $promoMessagesHelper,
        Store $store,
        DiscountCalculator $discountCalculator,
        PromoItemRegistry $promoItemRegistry,
        Http $httprequest
    ) {
        $this->checkoutSession = $resourceSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->store = $store;
        $this->fullDiscountItems = [];
        $this->discountCalculator = $discountCalculator;
        $this->promoItemRegistry = $promoItemRegistry;
        $this->httprequest = $httprequest;
    }

    /**
     * Add Items to Registry
     *
     * @param string|array $sku
     * @param int $qty
     * @param int $ruleId
     * @param array $discountData
     * @param int $type
     * @param string $discountAmount
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPromoItem($sku, $qty, $ruleId, $discountData, $type, $discountAmount)
    {
        $discountData = $this->getCurrencyDiscount($discountData);

        $autoAdd = false;
        $request = $this->httprequest;
    
        $graphrequest = $request->getContent();
        if (is_array($sku) && count($sku) === 1) {
            // if rule with behavior 'one of' have only single product item,
            // then behavior should be the same as rule 'all'
            $sku = $sku[0];
        }

        if (!is_array($sku)) {
            if (!$this->isProductValid($sku)) {
                return;
            }

            $item = $this->promoItemRegistry->getItemBySkuAndRuleId($sku, $ruleId);

            if ($item === null && $this->discountCalculator->isEnableAutoAdd($discountData)) {
                $autoAdd = $this->isProductCanBeAutoAdded($sku);
            }

            $item = $this->promoItemRegistry->registerItem(
                $sku,
                $qty,
                $ruleId,
                $type,
                $discountData['minimal_price'],
                $discountData['discount_item'],
                $discountAmount
            );

            if ($autoAdd) {
                /* condition starts for restrict auto add free gift product during
                remove free gift item updateCartItems*/
                if (!strpos($graphrequest, "removeItemFromCart") !== false &&
                    !strpos($graphrequest, "updateCartItems") !== false) {
                    $item->setAutoAdd($autoAdd);
                }
                /* condition ends for restrict auto add free gift product during remove free gift item*/
            }
        } else {
            foreach ($sku as $key => $skuValue) {
                if (!$this->isProductValid($skuValue)) {
                    unset($sku[$key]);
                    continue;
                }

                $this->promoItemRegistry->registerItem(
                    $skuValue,
                    $qty,
                    $ruleId,
                    $type,
                    $discountData['minimal_price'],
                    $discountData['discount_item'],
                    $discountAmount
                );
            }
        }

        if ($this->discountCalculator->isFullDiscount($discountData)) {
            if (!is_array($sku)) {
                $sku = [$sku];
            }

            foreach ($sku as $itemSku) {
                $this->fullDiscountItems[$itemSku]['rule_ids'][$ruleId] = $ruleId;
            }
        }

        $this->checkoutSession->setAmpromoFullDiscountItems($this->fullDiscountItems);
    }

    /**
     * IsProductValid
     *
     * @param string $sku
     * @return bool
     */
    private function isProductValid(string $sku): bool
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToFilter('sku', $sku);
        $product = $productCollection->getFirstItem();

        $currentWebsiteId = $this->storeManager->getWebsite()->getId();
        if (!is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            // Ignore products from other websites
            return false;
        }

        if (!$product || !$product->isInStock() || !$product->isSalable()) {
            $this->promoMessagesHelper->addAvailabilityError($product);

            return false;
        }

        return true;
    }

    /**
     * IsProductCanBeAutoAdded
     *
     * @param string $sku
     *
     * @return bool
     */
    private function isProductCanBeAutoAdded(string $sku): bool
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);

        if ((in_array($product->getTypeId(), static::AUTO_ADD_PRODUCT_TYPES)
            && !$product->getTypeInstance(true)->hasRequiredOptions($product))
            || $product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ) {
            return true;
        }

        return false;
    }

    /**
     * GetCurrencyDiscount
     *
     * @param array $discountData
     * @return array
     */
    private function getCurrencyDiscount($discountData)
    {
        preg_match('/^-*\d+.*\d*$/', $discountData['discount_item'] ?? 0, $discount);
        if (isset($discount[0]) && is_numeric($discount[0])) {
            $discountData['discount_item'] = $discount[0] * $this->store->getCurrentCurrencyRate();
        }

        return $discountData;
    }
}
