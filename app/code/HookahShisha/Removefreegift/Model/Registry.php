<?php
declare(strict_types=1);

namespace HookahShisha\Removefreegift\Model;

use Amasty\Promo\Model\Product;
use HookahShisha\Removefreegift\Model\Quote\SalesRule;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManager;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Promo\Helper\Item;
use Amasty\Promo\Helper\Messages;
use Magento\Store\Model\Store;
use Amasty\Promo\Model\DiscountCalculator;
use Magento\Framework\App\Request\Http;
use Amasty\Promo\Model\PromoItemRepository;

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
     * @var SessionManager
     */
    private $checkoutSession;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Item
     */
    protected $promoItemHelper;

    /**
     * @var Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var array
     */
    protected $fullDiscountItems;

    /**
     * @var DiscountCalculator
     */
    protected $discountCalculator;


    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Http
     */
    protected $httprequest;
    /**
     * @var Session|SessionManager
     */
    protected $resourceSession;

    protected Product $product;

    protected PromoItemRepository $promoItemRepository;
    private SalesRule $salesRule;

    /**
     * @param SessionManager|Session $resourceSession
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Item $promoItemHelper
     * @param Messages $promoMessagesHelper
     * @param Store $store
     * @param Product $product
     * @param DiscountCalculator $discountCalculator
     * @param PromoItemRepository $promoItemRepository
     * @param Http $httprequest
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        SessionManager           $resourceSession,
        ProductRepository        $productRepository,
        StoreManagerInterface    $storeManager,
        Item                     $promoItemHelper,
        Messages                 $promoMessagesHelper,
        Store                    $store,
        Product                  $product,
        DiscountCalculator       $discountCalculator,
        PromoItemRepository      $promoItemRepository,
        Http                     $httprequest,
        ProductCollectionFactory $productCollectionFactory,
        SalesRule                $salesRule
    )
    {
        parent::__construct(
            $resourceSession,
            $productRepository,
            $storeManager,
            $promoItemHelper,
            $promoMessagesHelper,
            $store,
            $product,
            $discountCalculator,
            $promoItemRepository
        );
        $this->checkoutSession = $resourceSession;
        $this->productRepository = $productRepository;
        $this->fullDiscountItems = [];
        $this->storeManager = $storeManager;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->store = $store;
        $this->product = $product;
        $this->discountCalculator = $discountCalculator;
        $this->promoItemRepository = $promoItemRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->httprequest = $httprequest;
        $this->salesRule = $salesRule;
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
     * @return array|false
     * @throws LocalizedException
     */
    public function addPromoItem($sku, $qty, $ruleId, $discountData, $type, $discountAmount, int $quoteId = null)
    {
        //Check Whether the rule id is already applied and, it is the correct rule id
        if ($this->salesRule->getSalesRuleIdByQuote($quoteId, $ruleId, $sku)) {
            $discountData = $this->getCurrencyDiscount($discountData);
            $autoAdd = false;
            $request = $this->httprequest;
            $graphrequest = $request->getContent();
            if (is_array($sku) && count($sku) === 1) {
                // if rule with behavior 'one of' have only single product item,
                // then behavior should be the same as rule 'all'
                $sku = $sku[0];
            }

            if (!$quoteId) {
                $quoteId = $this->checkoutSession->getQuote()->getId();
            }

            $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quoteId);

            if (!is_array($sku)) {
                if (!$this->isProductValid($sku)) {
                    return false;
                }
                $item = $promoItemsGroup->getItemBySkuAndRuleId($sku, $ruleId);
                if ($item === null && $this->discountCalculator->isEnableAutoAdd($discountData)) {
                    $autoAdd = $this->isProductCanBeAutoAdded($sku);
                }
                $item = $promoItemsGroup->registerItem(
                    $sku,
                    $qty,
                    $ruleId,
                    $type,
                    $discountData['minimal_price'],
                    $discountData['discount_item'],
                    $discountAmount
                );

                /* condition starts for restrict auto add free gift product
                   during remove free gift item updateCartItems*/
                /** removed condition for skip adding Free product on updateCartItems action. JIRA ID: OOKA-205 */
                if ($autoAdd && !strpos($graphrequest, "removeItemFromCart") !== false) {
                    $item->setAutoAdd($autoAdd);
                }
                /* condition ends for restrict auto add free gift product during remove free gift item*/
            } else {
                foreach ($sku as $key => $skuValue) {
                    if (!$this->isProductValid($skuValue)) {
                        unset($sku[$key]);
                        continue;
                    }
                    $promoItemsGroup->registerItem(
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
    }

    /**
     * IsProductValid
     *
     * @param string $sku
     * @return bool
     * @throws LocalizedException
     */
    private function isProductValid(string $sku): bool
    {
        /** @var Product $product */
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
        if (!$product->isInStock() || !$product->isSalable()) {
            $this->promoMessagesHelper->addAvailabilityError($product);
            return false;
        }
        return true;
    }

    /**
     * IsProductCanBeAutoAdded
     *
     * @param string $sku
     * @return bool
     * @throws NoSuchEntityException
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
     * @throws LocalizedException
     */
    private function getCurrencyDiscount($discountData): array
    {
        preg_match('/^-*\d+.*\d*$/', $discountData['discount_item'] ?? '', $discount);
        if (isset($discount[0]) && is_numeric($discount[0])) {
            $discountData['discount_item'] = $discount[0] * $this->store->getCurrentCurrencyRate();
        }
        return $discountData;
    }
}
