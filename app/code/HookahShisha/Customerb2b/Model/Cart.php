<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace HookahShisha\Customerb2b\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\DataObject;

/**
 * Shopping cart model
 *
 * @api
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @deprecated 100.1.0 Use \Magento\Quote\Model\Quote instead
 * @see \Magento\Quote\Api\Data\CartInterface
 * @since 100.0.2
 */
class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * Shopping cart items summary quantity(s)
     *
     * @var int|null
     */
    protected $_summaryQty;

    /**
     * List of product ids in shopping cart
     *
     * @var int[]|null
     */
    protected $_productIds;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\ResourceModel\Cart
     */
    protected $_resourceCart;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private $requestInfoFilter;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart $resourceCart
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Response\RedirectInterface $redirectInterface
     * @param array $data
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceCart = $resourceCart;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->RedirectInterface = $redirectInterface;
        parent::__construct($eventManager, $scopeConfig, $storeManager, $resourceCart, $checkoutSession, $customerSession, $messageManager, $stockRegistry, $stockState, $quoteRepository, $productRepository, $data);
    }
    /**
     * Get shopping cart resource model
     *
     * @return \Magento\Checkout\Model\ResourceModel\Cart
     * @codeCoverageIgnore
     */
    protected function _getResource()
    {
        return $this->_resourceCart;
    }

    /**
     * Retrieve checkout session model
     *
     * @return Session
     * @codeCoverageIgnore
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     * @codeCoverageIgnore
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * List of shopping cart items
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|array
     */
    public function getItems()
    {
        if (!$this->getQuote()->getId()) {
            return [];
        }
        return $this->getQuote()->getItemsCollection();
    }

    /**
     * Retrieve array of cart product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        $products = $this->getData('product_ids');
        if ($products === null) {
            $products = [];
            foreach ($this->getQuote()->getAllItems() as $item) {
                $products[$item->getProductId()] = $item->getProductId();
            }
            $this->setData('product_ids', $products);
        }
        return $products;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $this->setData('quote', $this->_checkoutSession->getQuote());
        }
        return $this->_getData('quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->setData('quote', $quote);
        return $this;
    }
    /**
     * Get request for product add to cart procedure
     *
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        $this->getRequestInfoFilter()->filter($request);

        return $request;
    }
    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();

        if ($productId) {
            $request = $this->getQtyRequest($product, $requestInfo);
            try {
                $this->_eventManager->dispatch(
                    'checkout_cart_product_add_before',
                    ['info' => $requestInfo, 'product' => $product]
                );
                $result = $this->getQuote()->addProduct($product, $request);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $this->RedirectInterface->getRefererUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Adding products to cart by ids
     *
     * @param  int[] $productIds
     * @return $this
     */
    public function addProductsByIds($productIds)
    {
        $allAvailable = true;
        $allAdded = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int) $productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    $request = $this->getQtyRequest($product);
                    try {
                        $this->getQuote()->addProduct($product, $request);
                    } catch (\Exception $e) {
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }

            if (!$allAvailable) {
                $this->messageManager->addErrorMessage(__("We don't have some of the products you want."));
            }
            if (!$allAdded) {
                $this->messageManager->addErrorMessage(__("We don't have as many of some products as you want."));
            }
        }
        return $this;
    }
    /**
     * Returns suggested quantities for items.
     * Can be used to automatically fix user entered quantities before updating cart
     * so that cart contains valid qty values
     *
     * The $data is an array of ($quoteItemId => (item info array with 'qty' key), ...)
     *
     * @param   array $data
     * @return  array
     */
    public function suggestItemsQty($data)
    {
        foreach ($data as $itemId => $itemInfo) {
            if (!isset($itemInfo['qty'])) {
                continue;
            }
            $qty = (float) $itemInfo['qty'];
            if ($qty <= 0) {
                continue;
            }

            $quoteItem = $this->getQuote()->getItemById($itemId);
            if (!$quoteItem) {
                continue;
            }

            $product = $quoteItem->getProduct();
            if (!$product) {
                continue;
            }

            $data[$itemId]['before_suggest_qty'] = $qty;
            $data[$itemId]['qty'] = $this->stockState->suggestQty(
                $product->getId(),
                $qty,
                $product->getStore()->getWebsiteId()
            );
        }
        return $data;
    }
    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->_checkoutSession->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $this->_checkoutSession->getQuote();
            $quoteId = $this->_checkoutSession->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_summaryQty = $useQty ? $this->getItemsQty() : $this->getItemsCount();
        }
        return $this->_summaryQty;
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount() * 1;
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     * @codeCoverageIgnore
     */
    public function getItemsQty()
    {
        return $this->getQuote()->getItemsQty() * 1;
    }
    /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Framework\DataObject
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @param int $itemId
     * @param int|array|\Magento\Framework\DataObject $requestInfo
     * @param null|array|\Magento\Framework\DataObject $updatingParams
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\Quote\Model\Quote::updateItem()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($productId) {
                $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
                $minimumQty = $stockItem->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty
                    && $minimumQty > 0
                    && !$request->getQty()
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_checkoutSession->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->_checkoutSession->getUseNotice() === null) {
                $this->_checkoutSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_update_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $result;
    }

    /**
     * Getter for RequestInfoFilter
     *
     * @deprecated 100.1.2
     * @return \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
        }
        return $this->requestInfoFilter;
    }

    /**
     * Get request quantity
     *
     * @param Product $product
     * @param \Magento\Framework\DataObject|int|array $request
     * @return int|DataObject
     */
    private function getQtyRequest($product, $request = 0)
    {
        $request = $this->_getProductRequest($request);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minimumQty = $stockItem->getMinSaleQty();
        //If product quantity is not specified in request and there is set minimal qty for it
        if ($minimumQty
            && $minimumQty > 0
            && !$request->getQty()
        ) {
            $request->setQty($minimumQty);
        }

        return $request;
    }
}
