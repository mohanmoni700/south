<?php
declare (strict_types = 1);

namespace HookahShisha\Avalara\Model\Tax\Sales\Total\Quote;

use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Psr\Log\LoggerInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Tax\Model\Config;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Tax\Helper\Data;
use Avalara\Excise\Model\ProcessTaxQuote;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory;
use Avalara\Excise\Model\Tax\TaxCalculation;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Avalara\Excise\Model\Logger;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tax extends \Avalara\Excise\Model\Tax\Sales\Total\Quote\Tax
{
    /**
     * Registry key to track whether AvaTax GetTaxRequest was successful
     */
    public const AVATAX_GET_TAX_REQUEST_ERROR = 'avatax_get_tax_request_error';

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
     * @param Config $taxConfig
     * @param TaxCalculationInterface $taxCalculationService
     * @param QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param AddressInterfaceFactory $customerAddressFactory
     * @param RegionInterfaceFactory $customerAddressRegionFactory
     * @param Data $taxData
     * @param ProcessTaxQuote $processTaxQuote
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param QuoteDetailsItemExtensionFactory $extensionFactory
     * @param TaxCalculation $taxCalculation
     * @param ProductRepositoryInterface $productRepository
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param Logger $logger
     * @param DataObjectFactory $dataObject
     * @param LoggerInterface $loggerInterface
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Config $taxConfig,
        TaxCalculationInterface $taxCalculationService,
        QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        AddressInterfaceFactory $customerAddressFactory,
        RegionInterfaceFactory $customerAddressRegionFactory,
        Data $taxData,
        ProcessTaxQuote $processTaxQuote,
        ScopeConfigInterface $scopeConfig,
        PriceCurrencyInterface $priceCurrency,
        QuoteDetailsItemExtensionFactory $extensionFactory,
        TaxCalculation $taxCalculation,
        ProductRepositoryInterface $productRepository,
        ExciseTaxConfig $exciseTaxConfig,
        Logger $logger,
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
            $taxData,
            $processTaxQuote,
            $scopeConfig,
            $priceCurrency,
            $extensionFactory,
            $taxCalculation,
            $productRepository,
            $exciseTaxConfig,
            $logger,
            $dataObject,
            $loggerInterface
        );
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

        if ($item->getHasChildren() &&
            (
                $item->getProductType() == "configurable" ||
                (
                    $item->getProductType() == "bundle" &&
                    $item->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED
                )
            )
        ) {
            $extensionAttributes = $itemDataObject->getExtensionAttributes() ?? $this->extensionFactory->create();

            $taxamount = $taxrate = $salesTax = $exciseTax = 0;

            foreach ($item->getChildren() as $child) {
                $lineItemTaxs = $this->processTaxQuote->getResponseLineItem($child->getId());
                if (is_array($lineItemTaxs) && count($lineItemTaxs)) {
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
        } else {
            $lineItemTaxs = $this->processTaxQuote->getResponseLineItem($itemId);

            $extensionAttributes = $itemDataObject->getExtensionAttributes() ?? $this->extensionFactory->create();

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
        }
        
        return $itemDataObject;
    }
}
