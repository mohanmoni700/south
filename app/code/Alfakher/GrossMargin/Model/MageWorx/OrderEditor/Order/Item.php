<?php

namespace Alfakher\GrossMargin\Model\MageWorx\OrderEditor\Order;

/**
 * @author af_bv_op
 */
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\Link as DownloadableLinkModel;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\Link\Purchased\ItemFactory as PurchasedItemFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory as LinkPurchasedCollectionFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory as LinkPurchasedItemCollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject\Copy as DataObjectCopy;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteItemToOrderItemConverter;
use Magento\Quote\Model\Quote\Item\Updater;
use Magento\Quote\Model\Quote\Item\Updater as QuoteItemUpdater;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\OrderEditor\Api\ChangeLoggerInterface;
use MageWorx\OrderEditor\Api\Data\LogMessageInterfaceFactory;
use MageWorx\OrderEditor\Api\OrderItemRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteItemRepositoryInterface;
use MageWorx\OrderEditor\Api\StockManagerInterface;
use MageWorx\OrderEditor\Api\TaxManagerInterface;
use MageWorx\OrderEditor\Helper\Data as Helper;
use MageWorx\OrderEditor\Model\Edit\QuoteFactory as OrderEditorQuoteFactory;
use MageWorx\OrderEditor\Model\Invoice as OrderEditorInvoice;

class Item extends \MageWorx\OrderEditor\Model\Order\Item
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param Helper $helper
     * @param PurchasedFactory $purchasedFactory
     * @param PurchasedItemFactory $purchasedItemFactory
     * @param LinkPurchasedCollectionFactory $linkPurchasedCollectionFactory
     * @param LinkPurchasedItemCollectionFactory $linkPurchasedItemsCollectionFactory
     * @param DataObjectCopy $objectCopyService
     * @param DownloadableLinkModel $downloadableLink
     * @param OrderEditorInvoice $invoice
     * @param TaxManagerInterface $taxManager
     * @param TransactionFactory $transactionFactory
     * @param QuoteItemRepositoryInterface $quoteItemRepository
     * @param MessageManagerInterface $messageManager
     * @param OrderEditorQuoteFactory $orderEditorQuoteFactory
     * @param OrderItemRepositoryInterface $oeOrderItemRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param QuoteItemToOrderItemConverter $quoteItemToOrderItemConverter
     * @param SerializerJson $serializerJson
     * @param Updater $quoteItemUpdater
     * @param StockManagerInterface $stockManager
     * @param LogMessageInterfaceFactory $logMessageFactory
     * @param ChangeLoggerInterface $changeLogger
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        OrderFactory $orderFactory,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        Helper $helper,
        PurchasedFactory $purchasedFactory,
        PurchasedItemFactory $purchasedItemFactory,
        LinkPurchasedCollectionFactory $linkPurchasedCollectionFactory,
        LinkPurchasedItemCollectionFactory $linkPurchasedItemsCollectionFactory,
        DataObjectCopy $objectCopyService,
        DownloadableLinkModel $downloadableLink,
        OrderEditorInvoice $invoice,
        TaxManagerInterface $taxManager,
        TransactionFactory $transactionFactory,
        QuoteItemRepositoryInterface $quoteItemRepository,
        MessageManagerInterface $messageManager,
        OrderEditorQuoteFactory $orderEditorQuoteFactory,
        OrderItemRepositoryInterface $oeOrderItemRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        QuoteItemToOrderItemConverter $quoteItemToOrderItemConverter,
        SerializerJson $serializerJson,
        Updater $quoteItemUpdater,
        StockManagerInterface $stockManager,
        LogMessageInterfaceFactory $logMessageFactory,
        ChangeLoggerInterface $changeLogger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->purchasedFactory = $purchasedFactory;
        $this->purchasedItemFactory = $purchasedItemFactory;
        $this->helper = $helper;
        $this->linkPurchasedCollectionFactory = $linkPurchasedCollectionFactory;
        $this->linkPurchasedItemsCollectionFactory = $linkPurchasedItemsCollectionFactory;
        $this->objectCopyService = $objectCopyService;
        $this->downloadableLink = $downloadableLink;
        $this->invoice = $invoice;
        $this->taxManager = $taxManager;
        $this->transactionFactory = $transactionFactory;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->messageManager = $messageManager;
        $this->orderEditorQuoteFactory = $orderEditorQuoteFactory;
        $this->oeOrderItemRepository = $oeOrderItemRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->quoteItemToOrderItemConverter = $quoteItemToOrderItemConverter;
        $this->serializerJson = $serializerJson;
        $this->quoteItemUpdater = $quoteItemUpdater;
        $this->stockManager = $stockManager;
        $this->logMessageFactory = $logMessageFactory;
        $this->changeLogger = $changeLogger;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $orderFactory,
            $storeManager,
            $productRepository,
            $helper,
            $purchasedFactory,
            $purchasedItemFactory,
            $linkPurchasedCollectionFactory,
            $linkPurchasedItemsCollectionFactory,
            $objectCopyService,
            $downloadableLink,
            $invoice,
            $taxManager,
            $transactionFactory,
            $quoteItemRepository,
            $messageManager,
            $orderEditorQuoteFactory,
            $oeOrderItemRepository,
            $searchCriteriaBuilderFactory,
            $quoteItemToOrderItemConverter,
            $serializerJson,
            $quoteItemUpdater,
            $stockManager,
            $logMessageFactory,
            $changeLogger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Update Item Data
     */
    protected function updateItemData()
    {
        $changes = [];

        /* af_bv_op; Start */
        // gross margin
        if (isset($this->newParams['gross_margin'])) {
            $this->setGrossMargin($this->newParams['gross_margin']);
        }
        /* af_bv_op; End */

        // description
        if (isset($this->newParams['description'])) {
            $this->setDescription($this->newParams['description']);
        }

        // tax amount
        if (isset($this->newParams['tax_amount'])) {
            $taxAmount = (float) $this->newParams['tax_amount'];
            $baseTaxAmount = $this->currencyConvertToBaseCurrency($taxAmount);
            $origTaxAmount = $this->getTaxAmount();

            $this->setBaseTaxAmount($baseTaxAmount)
                ->setTaxAmount($taxAmount)
                ->setBaseTaxInvoiced(0)
                ->setTaxInvoiced(0);

            if ($origTaxAmount != $taxAmount) {
                $changes[] = __(
                    'Tax Amount has been changed from <b>%1</b> to <b>%2</b>',
                    $this->getOrder()->formatPriceTxt($origTaxAmount),
                    $this->getOrder()->formatPriceTxt($taxAmount)
                );
            }
        }

        // discount tax compensation amount
        if (isset($this->newParams['discount_tax_compensation_amount'])) {
            $hiddenTax = (float) $this->newParams['discount_tax_compensation_amount'];
            $baseHiddenTax = $this->currencyConvertToBaseCurrency($hiddenTax);

            $this->setBaseDiscountTaxCompensationAmount($baseHiddenTax)
                ->setDiscountTaxCompensationAmount($hiddenTax);
        }

        // tax percent
        if (isset($this->newParams['tax_percent'])) {
            $origValue = $this->getTaxPercent();
            $this->setTaxPercent($this->newParams['tax_percent']);

            if ($origValue != $this->newParams['tax_percent']) {
                $changes[] = __(
                    'Tax Percent has been changed from <b>%1</b> to <b>%2</b>',
                    round($origValue, 2),
                    round($this->newParams['tax_percent'], 2)
                );
            }
        }

        // price
        if (isset($this->newParams['price'])) {
            $price = (float) $this->newParams['price'];
            $basePrice = $this->currencyConvertToBaseCurrency($price);
            $origPrice = $this->getPrice();

            $this->setBasePrice($basePrice)
                ->setPrice($price);

            if ($origPrice != $price) {
                $changes[] = __(
                    'Price has been changed from <b>%1</b> to <b>%2</b>',
                    $this->getOrder()->formatPriceTxt($origPrice),
                    $this->getOrder()->formatPriceTxt($price)
                );
            }
        }

        // Price includes tax
        if (isset($this->newParams['price_incl_tax'])) {
            $priceInclTax = (float) $this->newParams['price_incl_tax'];
            $basePriceInclTax = $this->currencyConvertToBaseCurrency($priceInclTax);

            $this->setBasePriceInclTax($basePriceInclTax)
                ->setPriceInclTax($priceInclTax);
        }

        // discount amount
        if (isset($this->newParams['discount_amount'])) {
            $discountAmount = (float) $this->newParams['discount_amount'];
            $baseDiscountAmount = $this->currencyConvertToBaseCurrency($discountAmount);
            $origDiscountAmount = $this->getDiscountAmount();

            $this->setBaseDiscountAmount($baseDiscountAmount)
                ->setDiscountAmount($discountAmount)
                ->setBaseDiscountInvoiced(0)
                ->setDiscountInvoiced(0);

            if ($origDiscountAmount != $discountAmount) {
                $changes[] = __(
                    'Discount has been changed from <b>%1</b> to <b>%2</b>',
                    $this->getOrder()->formatPriceTxt($origDiscountAmount),
                    $this->getOrder()->formatPriceTxt($discountAmount)
                );
            }
        }

        // discount percent
        if (isset($this->newParams['discount_percent'])) {
            $this->setDiscountPercent($this->newParams['discount_percent']);
        }

        // subtotal (row total)
        if (isset($this->newParams['subtotal'])) {
            $currentSubtotal = (float) $this->newParams['subtotal'];
            $baseCurrencySubtotal = $this->currencyConvertToBaseCurrency($currentSubtotal);
            $roundBaseCurrencySubtotal = $this->helper->roundAndFormatPrice($baseCurrencySubtotal);

            $this->setBaseRowTotal($roundBaseCurrencySubtotal)
                ->setRowTotal($currentSubtotal)
                ->setBaseRowInvoiced(0)
                ->setRowInvoiced(0);
        }

        // Subtotal includes tax
        if (isset($this->newParams['subtotal_incl_tax'])) {
            $subtotalInclTax = (float) $this->newParams['subtotal_incl_tax'];
            $baseCurrencySubtotalInclTax = $this->currencyConvertToBaseCurrency($subtotalInclTax);
            $roundBaseCurrencySubtotalInclTax = $this->helper->roundAndFormatPrice($baseCurrencySubtotalInclTax);

            $this->setBaseRowTotalInclTax($roundBaseCurrencySubtotalInclTax)
                ->setRowTotalInclTax($subtotalInclTax);
        }

        try {
            $this->oeOrderItemRepository->save($this);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Error while updating item data: %1', $e->getMessage())
            );

            throw $e;
        }

        if (!empty($changes)) {
            $logMessages = [];
            foreach ($changes as $changeMessage) {
                $logMessages[] = $this->logMessageFactory->create(
                    ['message' => $changeMessage, 'level' => 2]
                );
            }

            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::MESSAGES_KEY => $logMessages,
                    ChangeLoggerInterface::GROUP_CODE => 'item_' . $this->getId(),
                    ChangeLoggerInterface::TYPE_CODE => ChangeLoggerInterface::TYPE_ITEM,
                ]
            );
        }
    }
}
