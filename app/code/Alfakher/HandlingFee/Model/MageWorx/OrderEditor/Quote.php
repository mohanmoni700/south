<?php

namespace Alfakher\HandlingFee\Model\MageWorx\OrderEditor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType as AbstractProductType;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as OriginalQuote;
use Magento\Quote\Model\Quote\Item as OriginalQuoteItem;
use MageWorx\OrderEditor\Model\Quote\Item as OrderEditorQuoteItem;

class Quote extends \MageWorx\OrderEditor\Model\Quote
{
    /**
     * @var skipItemErrors|false
     */
    private $skipItemErrors = false;

    /**
     * Quote constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param OriginalQuote\AddressFactory $quoteAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param OriginalQuote\ItemFactory $quoteItemFactory
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param OriginalQuote\PaymentFactory $quotePaymentFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory
     * @param DataObject\Copy $objectCopyService
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param OriginalQuoteItem\Processor $itemProcessor
     * @param DataObject\Factory $objectFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param OriginalQuote\TotalsCollector $totalsCollector
     * @param OriginalQuote\TotalsReader $totalsReader
     * @param \Magento\Quote\Model\ShippingFactory $shippingFactory
     * @param \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory
     * @param Quote\ItemFactory $orderEditorQuoteItemFactory
     * @param ResourceModel\Quote\Item\CollectionFactory $oeQuoteItemCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \MageWorx\OrderEditor\Model\Quote\ItemFactory $orderEditorQuoteItemFactory,
        \MageWorx\OrderEditor\Model\ResourceModel\Quote\Item\CollectionFactory $oeQuoteItemCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->allItemsAreNew              = false;
        $this->orderEditorQuoteItemFactory = $orderEditorQuoteItemFactory;
        $this->_quoteItemCollectionFactory = $oeQuoteItemCollectionFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $quoteValidator,
            $catalogProduct,
            $scopeConfig,
            $storeManager,
            $config,
            $quoteAddressFactory,
            $customerFactory,
            $groupRepository,
            $quoteItemCollectionFactory,
            $quoteItemFactory,
            $messageFactory,
            $statusListFactory,
            $productRepository,
            $quotePaymentFactory,
            $quotePaymentCollectionFactory,
            $objectCopyService,
            $stockRegistry,
            $itemProcessor,
            $objectFactory,
            $addressRepository,
            $criteriaBuilder,
            $filterBuilder,
            $addressDataFactory,
            $customerDataFactory,
            $customerRepository,
            $dataObjectHelper,
            $extensibleDataObjectConverter,
            $currencyFactory,
            $extensionAttributesJoinProcessor,
            $totalsCollector,
            $totalsReader,
            $shippingFactory,
            $shippingAssignmentFactory,
            $orderEditorQuoteItemFactory,
            $oeQuoteItemCollectionFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Convert Product To QuoteItem
     *
     * @param Product $product
     * @param DataObject $request
     * @return $item
     */
    private function convertProductToQuoteItem(
        Product $product,
        DataObject $request
    ): OrderEditorQuoteItem {
        /** @var CartItemInterface|OrderEditorQuoteItem $item */
        $item = $this->orderEditorQuoteItemFactory->create();
        $item->setStoreId($this->getStoreId());
        $item->setOptions($product->getCustomOptions());
        $item->setQuote($this);
        $item->setProduct($product);

        if ($request->getResetCount()
            && !$product->getStickWithinParent()
            && $item->getId() === $request->getId()
        ) {
            $item->setData(CartItemInterface::KEY_QTY, 0);
        }

        return $item;
    }

    /**
     * Add Product
     *
     * @param Product $product
     * @param null|float|\Magento\Framework\DataObject $request
     * @param null|string $processMode
     * @return bool|OriginalQuoteItem|null
     * @throws \Exception
     */
    public function addProduct(
        Product $product,
        $request = null,
        $processMode = AbstractProductType::PROCESS_MODE_FULL
    ) {
        $this->lastErrorItem = null;

        if ($request === null) {
            $request = 1;
        }

        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }

        if (!$request instanceof DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if ($request->getData('options')) {
            /**
             * Prevent error: invalid argument in implode method
             * @see \Magento\Catalog\Model\Product\Option\Type\Select::getOptionSku()
             */
            $this->convertArrayOptionValueToString($request);
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);
        if (is_string($cartCandidates) || $cartCandidates instanceof Phrase) {
            $exMsg = $cartCandidates instanceof Phrase ? $cartCandidates : __($cartCandidates);
            throw new LocalizedException($exMsg);
        }

        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        /** @var \MageWorx\OrderEditor\Model\Quote\Item|null $parentItem */
        $parentItem = null;
        $errors     = [];
        $item       = null;
        $items      = [];
        foreach ($cartCandidates as $candidate) {
            /**
             * $stickWithinParent must be Quote Item instance
             *
             * @var \MageWorx\OrderEditor\Model\Quote\Item $stickWithinParent
             */
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->getItemByProduct($candidate);
            if (!$item) {
                $item = $this->convertProductToQuoteItem(
                    $candidate,
                    $request
                ); // @TODO Check this method, compare with original logic
                $this->addItem($item);
            }

            $item->setIgnoreCartRules($this->getIgnoreCartRules());

            $items[] = $item;

            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            try {
                $request->setData('id', $item->getId());
                $candidate->setCartQty($request->getQty());
                $this->itemProcessor->prepare($item, $request, $candidate);
            } catch (InputException $exception) {
                $this->_logger->critical($exception->getMessage());
            } catch (LocalizedException $localizedException) {
                $this->_logger->critical($localizedException->getMessage());
            }

            if ($item->getHasError()) {
                /*bv_op; date : 27-7-22; error handling if item is out of stock; start*/
                
                /*
                if ($this->getSkipItemErrors()) {
                    $item->setHasError(false);
                    $item->clearMessage();
                } else {
                    $message = $item->getMessage();
                    if (!in_array($message, $errors)) {
                        $errors[] = $message;  // filter duplicate messages
                    }
                }
                */

                $item->setHasError(false);
                $item->clearMessage();
                /*bv_op; date : 27-7-22; error handling if item is out of stock; end*/
            }
        }

        if (!empty($errors)) {
            $this->lastErrorItem = $item;
            throw new LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);

        return $parentItem;
    }
}
