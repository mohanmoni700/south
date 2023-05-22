<?php

namespace Avalara\Excise\Model\Tax\Sales\Total\Quote;

use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    /**
     * Registry key to track whether AvaTax GetTaxRequest was successful
     */
    const AVATAX_GET_TAX_REQUEST_ERROR = 'avatax_get_tax_request_error';

    /**
     * @var Avalara\Excise\Model\ProcessTaxQuote
     */
    protected $processTaxQuote;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var Avalara\Excise\Model\Tax\TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Avalara\Excise\Model\Logger
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObject;

    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var LoggerInterface
     */
    protected $loggerInterface;

    /**
     * Undocumented function
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Avalara\Excise\Model\ProcessTaxQuote $processTaxQuote
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory
     * @param \Avalara\Excise\Model\Tax\TaxCalculation $taxCalculation
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param \Avalara\Excise\Model\Logger $logger
     * @param DataObjectFactory $dataObject
     * @param LoggerInterface $loggerInterface
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        \Avalara\Excise\Model\ProcessTaxQuote $processTaxQuote,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $extensionFactory,
        \Avalara\Excise\Model\Tax\TaxCalculation $taxCalculation,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ExciseTaxConfig $exciseTaxConfig,
        \Avalara\Excise\Model\Logger $logger,
        DataObjectFactory $dataObject,
        LoggerInterface $loggerInterface
    ) {
        $this->processTaxQuote = $processTaxQuote;
        $this->scopeConfig = $scopeConfig;
        $this->priceCurrency = $priceCurrency;
        $this->extensionFactory = $extensionFactory;
        $this->taxCalculation = $taxCalculation;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->loggerInterface = $loggerInterface;
        $this->dataObject = $dataObject;

        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory,
            $taxData
        );
    }

    /**
     * @codeCoverageIgnore
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $storeId = $quote->getStoreId();
        $isEnabled = $this->exciseTaxConfig->isModuleEnabled($storeId);
        
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $address = $shippingAssignment->getShipping()->getAddress();
        $storeId = $quote->getStoreId();
        $isAddressTaxable = $this->exciseTaxConfig->isAddressTaxable($address, $storeId);
        if (!$isEnabled || !$isAddressTaxable) {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        $baseTaxDetails = $this->getQuoteTaxDetailsInterface($shippingAssignment, $total, true);
        //$taxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, false);

        $this->processTaxQuote->getTaxForOrder($quote, $baseTaxDetails, $shippingAssignment);

        if ($this->processTaxQuote->isValidResponse()) {
            $quoteTax = $this->getQuoteTax($quote, $shippingAssignment, $total);

            //Populate address and items with tax calculation results
            $itemsByType = $this->organizeItemTaxDetailsByType($quoteTax['tax_details'], $quoteTax['base_tax_details']);

            if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
                $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
            }

            if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
                $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
                $baseShippingTaxDetails =
                    $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
                $this->processShippingTaxInfo(
                    $shippingAssignment,
                    $total,
                    $shippingTaxDetails,
                    $baseShippingTaxDetails
                );
            }

            //Process taxable items that are not product or shipping
            $this->processExtraTaxables($total, $itemsByType);

            //Save applied taxes for each item and the quote in aggregation
            $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

            if ($this->includeExtraTax()) {
                $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
                $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
            }
        } else {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        return $this;
    }

    /**
     * Get quote tax details
     *
     * @codeCoverageIgnore
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    protected function getQuoteTax(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $baseTaxDetailsInterface = $this->getQuoteTaxDetailsInterface($shippingAssignment, $total, true);
        $taxDetailsInterface = $this->getQuoteTaxDetailsInterface($shippingAssignment, $total, false);

        $baseTaxDetails = $this->getQuoteTaxDetailsOverride($quote, $baseTaxDetailsInterface, true);
        $taxDetails = $this->getQuoteTaxDetailsOverride($quote, $taxDetailsInterface, false);

        return [
            'base_tax_details' => $baseTaxDetails,
            'tax_details' => $taxDetails
        ];
    }

    /**
     * Get tax details interface based on the quote and items
     *
     * @codeCoverageIgnore
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsInterface
     */
    protected function getQuoteTaxDetailsInterface($shippingAssignment, $total, $useBaseCurrency)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $address->getQuote()->setExciseTax(0);
        $address->getQuote()->setSalesTax(0);
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);
        //$address->getQuote()->save();

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $shippingDataObject = $this->extendShippingItem($shippingDataObject);
            $itemDataObjects[] = $shippingDataObject;
        }

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemDataObjectFactory,
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);

        return $quoteDetails;
    }

    /**
     * Get quote tax details for calculation
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxDetails
     * @param bool $useBaseCurrency
     * @return array
     */
    public function getQuoteTaxDetailsOverride(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxDetails,
        $useBaseCurrency
    ) {
        $store = $quote->getStore();
        $taxDetails = $this->taxCalculation->calculateTaxDetails($taxDetails, $useBaseCurrency, $store);
        return $taxDetails;
    }

    /**
     * Map an item to item data object with product ID
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param bool $priceIncludesTax
     * @param bool $useBaseCurrency
     * @param string $parentCode
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function mapItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $itemDataObject = parent::mapItem(
            $itemDataObjectFactory,
            $item,
            $priceIncludesTax,
            $useBaseCurrency,
            $parentCode
        );

        $itemId = ($item->getQuote()->getIsMultiShipping()) ? $item->getQuoteItemId() : $item->getItemId();
        $lineItemTaxs = $this->processTaxQuote->getResponseLineItem($itemId);

        $extensionAttributes = $itemDataObject->getExtensionAttributes()
            ? $itemDataObject->getExtensionAttributes()
            : $this->extensionFactory->create();

        if (is_array($lineItemTaxs) && count($lineItemTaxs)) {
            $taxamount = $taxrate = $salesTax = $exciseTax = 0;
            foreach ($lineItemTaxs as $lineItemTax) {
                $taxamount += $lineItemTax['TaxAmount'];
                if (isset($lineItemTax['TaxRate'])) {
                    $taxrate += $lineItemTax['TaxRate'];
                } else {
                    if ($item->getPrice() > 0 && $lineItemTax['TaxAmount'] > 0) {
                        $tax_rate = $lineItemTax['TaxAmount'] / ($item->getPrice()*$item->getQty());
                        $taxrate += $tax_rate;
                    }
                }
                if ($lineItemTax['TaxType']=="S") {
                    $salesTax += $lineItemTax['TaxAmount'];
                } else {
                    $exciseTax += $lineItemTax['TaxAmount'];
                }
            }
            $item->setExciseTax($exciseTax);
            $item->setSalesTax($salesTax);
            $quoteExciseTax = $item->getQuote()->getExciseTax() + $exciseTax;
            $quoteSalesTax = $item->getQuote()->getSalesTax() + $salesTax;

            $item->getQuote()->setExciseTax($quoteExciseTax);
            $item->getQuote()->setSalesTax($quoteSalesTax);

            $taxCollectable = $this->priceCurrency->convertAndRound(
                $taxamount,
                $item->getQuote()->getStore(),
                $item->getQuote()->getCurrency()
            );
            $extensionAttributes->setData('excise_response', $item->getQuote()->getExciseTaxResponseOrder());
            $extensionAttributes->setData('tax_breakdown', json_encode($lineItemTaxs));
            $extensionAttributes->setData('tax_collectable', $taxCollectable);
            $extensionAttributes->setData('combined_tax_rate', ($taxrate * 100));
        }

        return $itemDataObject;
    }

    /**
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface
     * @codeCoverageIgnore
     */
    protected function extendShippingItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterface $shippingDataObject
    ) {
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes */
        $extensionAttributes = $shippingDataObject->getExtensionAttributes()
            ? $shippingDataObject->getExtensionAttributes()
            : $this->extensionFactory->create();

        $shippingTax = $this->processTaxQuote->getResponseShipping();

        if (is_array($shippingTax) && count($shippingTax)) {
            $taxamount = $taxrate = 0;
            foreach ($shippingTax as $lineItemTax) {
                $taxamount += $lineItemTax['TaxAmount'];
                $taxrate += $lineItemTax['TaxRate'];
            }

            $taxCollectable = $this->priceCurrency->convertAndRound(
                $taxamount
            );

            $extensionAttributes->setTaxCollectable($taxCollectable);
            $extensionAttributes->setCombinedTaxRate(($taxrate * 100));
            $extensionAttributes->setJurisdictionTaxRates([
                'shipping' => [
                    'id' => 'shipping',
                    'rate' => $taxrate * 100,
                    'amount' => $taxCollectable
                ]
            ]);

            $shippingDataObject->setExtensionAttributes($extensionAttributes);
        }
        return $shippingDataObject;
    }

    /**
     * @inheritDoc
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $totals = parent::fetch($quote, $total);
        if (empty($totals)) {
            return $totals;
        }
        $taxIncluded = (boolean)$this->scopeConfig->getValue(ExciseTaxConfig::XML_PATH_AVATAX_TAX_INCLUDED, ScopeInterface::SCOPE_STORES);
        if (!$taxIncluded) {
            return $totals;
        }
        foreach ($totals as &$total) {
            if (isset($total['code']) && $total['code'] == 'tax') {
                $total['title'] .= "(".__(ExciseTaxConfig::XML_SUFFIX_AVATAX_TAX_INCLUDED).")";
            }
        }
        return $totals;
    }

    /**
     * Get Tax label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        $label = parent::getLabel();
        $taxIncluded = (boolean)$this->scopeConfig->getValue(ExciseTaxConfig::XML_PATH_AVATAX_TAX_INCLUDED, ScopeInterface::SCOPE_STORES);
        if ($taxIncluded) {
            $label .= "(".__(ExciseTaxConfig::XML_SUFFIX_AVATAX_TAX_INCLUDED).")";
        }

        return $label;        
    }
}
