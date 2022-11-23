<?php
declare (strict_types = 1);

namespace HookahShisha\Avalara\Model;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\RegionFactory;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Avalara\Excise\Framework\Rest as ExciseClient;
use Avalara\Excise\Framework\RestFactory as ExciseClientFactory;
use Avalara\Excise\Framework\Constants;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\OrderRepository;
use Magento\Directory\Model\Country\Postcode\ConfigInterface;
use Magento\Tax\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session;
use Magento\Bundle\Model\Product\Price;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessTaxQuote extends \Avalara\Excise\Model\ProcessTaxQuote
{
    /**
     * Value for invoice transaction
     */
    public const SALE_INVOICE_TYPE = "INVOICE";

    /**
     * Value for refund transaction
     */
    public const SALE_REFUND_TYPE = "REFUND";

    /**
     * Value for API pass
     */
    public const APIPASS = "true";

    /**
     * Value for API retursns response as error
     */
    public const APIERROR = "apierror";

     /**
      * Value for API failed
      */
    public const APIFAIL = "false";

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var ExciseClient
     */
    protected $exciseClient;

    /**
     * @var ExciseClientFactory
     */
    protected $exciseClientFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Tax\Helper\Data $taxData
     */
    protected $taxData;

    /**
     * @var \Magento\Directory\Model\Country\Postcode\ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Zend_Http_Response
     */
    protected $response;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var string
     */
    protected $timestamp;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var \Magento\Sales\Model\OrderRepository;
     */
    protected $orderRepository;
    
    /**
     * [__construct]
     *
     * @param Session $checkoutSession
     * @param RegionFactory $regionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ExciseClient $exciseClient
     * @param ExciseClientFactory $exciseClientFactory
     * @param ProductMetadataInterface $productMetadata
     * @param Data $taxData
     * @param ConfigInterface $postCodesConfig
     * @param LoggerInterface $logger
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param Random $mathRandom
     * @param OrderRepository $orderRepository
     * @param ItemFactory $quoteItemFactory
     * @param CollectionFactory $quoteItemCollectionFactory
     */
    public function __construct(
        Session $checkoutSession,
        RegionFactory $regionFactory,
        ScopeConfigInterface $scopeConfig,
        ExciseClient $exciseClient,
        ExciseClientFactory $exciseClientFactory,
        ProductMetadataInterface $productMetadata,
        Data $taxData,
        ConfigInterface $postCodesConfig,
        LoggerInterface $logger,
        ExciseTaxConfig $exciseTaxConfig,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        CustomerRepositoryInterface $customerRepository,
        Random $mathRandom,
        OrderRepository $orderRepository,
        ItemFactory $quoteItemFactory,
        CollectionFactory $quoteItemCollectionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->regionFactory = $regionFactory;
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $scopeConfig;
        $this->exciseClient = $exciseClient;
        $this->exciseClientFactory = $exciseClientFactory;
        $this->taxData = $taxData;
        $this->postCodesConfig = $postCodesConfig;
        $this->logger = $logger;
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->shipLineNo = Constants::SHIP_LINE_NO;
        $this->dateTime = $exciseTaxConfig->getTimeZoneObject();
        $this->customerRepository = $customerRepository;
        $this->mathRandom = $mathRandom;
        $this->orderRepository = $orderRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        parent::__construct(
            $checkoutSession,
            $regionFactory,
            $scopeConfig,
            $exciseClient,
            $exciseClientFactory,
            $productMetadata,
            $taxData,
            $postCodesConfig,
            $logger,
            $exciseTaxConfig,
            $storeManager,
            $productRepository,
            $customerRepository,
            $mathRandom,
            $orderRepository
        );
    }

    /**
     * _getShippingLineItems
     *
     * @param mixed $lineitems
     * @param mixed $queueObject
     * @param mixed $lineCount
     * @param mixed $customerData
     * @param mixed $shippingAmount
     * @param mixed $isQuote
     * @param mixed $entityType
     * @return array
     */
    private function _getShippingLineItems(
        $lineitems,
        $queueObject,
        $lineCount,
        $customerData,
        $shippingAmount,
        $isQuote,
        $entityType
    ) {
        $lineCount++;
        $shippingCode = $this->exciseTaxConfig->getShippingCode();
        $shipAmount = empty($shippingAmount) ? 0 : $shippingAmount;
        $invoiceLine = $this->shipLineNo;
        if (!$isQuote) {
            $invoiceLine = $lineCount;
        }

        $BilledUnits = 1;
        
        if ($entityType == "creditmemo") {
            $saleType = self::SALE_REFUND_TYPE;
            $BilledUnits = -1;
        } elseif ($entityType == "invoice") {
            $saleType = self::SALE_INVOICE_TYPE;
        } else {
            $saleType = '';
        }
        $lineitems = [
            'InvoiceLine' => $invoiceLine,
            'ProductCode' => empty($shippingCode) ? "FR" : $shippingCode,
            'UnitPrice' => $shipAmount,
            'BilledUnits' => $BilledUnits,
            'LineAmount' => $shipAmount * $BilledUnits,
            'AlternateUnitPrice' => "",
            'AlternateLineAmount' => "",
            'Currency' => $lineitems['Currency'],
            'UnitOfMeasure' => "",
            'TaxIncluded' => $lineitems['TaxIncluded'],
            'AlternativeFuelContent' => "",
            'UserData' => $customerData['UserData'],
            'CustomString1' => "",
            'DestinationType' => '',
            'Destination' => '',
            'DestinationOutCityLimitInd' => 'N',
            'DestinationSpecialJurisdictionInd' => 'N',
            'DestinationExciseWarehouse' => null,
            'SaleType' => $saleType
        ];
        return $lineitems;
    }
    
    /**
     * Tax calculation for order
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return $this
     */
    public function getTaxForOrder(
        $quote,
        $quoteTaxDetails,
        $shippingAssignment
    ) {
        $obj = $this->exciseClient->getDataObject();
        $exeEndTime = $apiStartTime = $apiEndTime = 0;
        $exeStartTime = microtime(true);
        $apiType = Constants::EXCISE_API;
        $sendLog = true;

        $address = $shippingAssignment->getShipping()->getAddress();
        $storeId = $quote->getStoreId();

        $accountNo = $this->exciseTaxConfig->getAvaTaxAccountNumber($storeId);
        $licenseKey = $this->exciseTaxConfig->getAvaTaxLicenseKey($storeId);
        $companyId = $this->exciseTaxConfig->getExciseCompanyId($storeId);
        $mode = $this->exciseTaxConfig->isProductionMode($storeId);

        if (!$accountNo || !$licenseKey || !$companyId) {
            $details['account_number'] = $accountNo;
            $details['licenseKey'] = $licenseKey;
            $details['companyId'] = $companyId;
            $this->logger->critical('Invalid Excise tax credentials: ' . json_encode($details));
            return;
        }

        if (!$address->getPostcode()) {
            $this->logger->critical('null postal code');
            return;
        }

        if (!$this->_validatePostcode($address->getPostcode(), $address->getCountry())) {
            $postCode = $address->getPostcode();
            $countryId = $address->getCountry();
            $this->logger->critical('Invalid postalcode: ' . $postCode . ' - ' . $countryId);
            return;
        }

        if (!count($address->getAllItems())) {
            $this->logger->critical('No quote items');
            return;
        }

        $shipping = (float) $address->getShippingAmount();
        $shippingDiscount = (float) $address->getShippingDiscountAmount();

        $originLocation = $this->getOriginLocation($quote);

        $destinationLocation = $this->getDestinationLocation($address);

        $saleCountry = $this->getSaleCountry($quote);

        $customerData = $this->getCustomerData($quote);

        $lineParam = array_merge($originLocation, $destinationLocation, $saleCountry, $customerData);

        $lineItems = $this->_getLineItems($quote, $quoteTaxDetails, $lineParam, $customerData, $address);

        $obj->setLineCount(count($lineItems));
        $entityUseCode = $this->getEntityUseCode($quote);
        $obj->setDocCode($quote->getId());
        $cartItems = [];
        $cartItems['TransactionLines'] = $lineItems;
        $cartItems['EffectiveDate'] = $quote->getUpdatedAt();
        $cartItems['InvoiceDate'] = $quote->getUpdatedAt();
        $cartItems['InvoiceNumber'] = $quote->getId();
        $cartItems['TitleTransferCode'] = 'DEST';
        $cartItems['TransactionType'] = $this->getCustomerType($quote);
        $cartItems['Seller'] = $this->storeManager->getStore()->getName();
        $randNumber = $this->mathRandom->getRandomNumber(0, 9999);
        $custId = $quote->getCustomerId() ? $quote->getCustomerId() : "Gust-".$randNumber;
        $cartItems['Buyer'] = $custId;
        $cartItems['UserData'] = $customerData['UserData'];
        $cartItems['UserTranId'] = $quote->getId();
        $cartItems['SourceSystem'] = Constants::SOURCE_SYSTEM;
        $cartItems['CommitStatus'] = "TEMPORARY";
        $cartItems['EntityUseCode'] = $entityUseCode;

        if ($this->orderChanged($cartItems)) {
            try {
                $apiStartTime = microtime(true);
                $response = $this->exciseClient->createExciseTaxTransaction($storeId, $cartItems);
                $apiEndTime = microtime(true);
                $this->response = $response;

                $quote->setExciseTaxResponseOrder(json_encode($this->response));

                if ($this->response['Status'] == "Success") {
                    // store request in session
                    $cacheItems = $cartItems;
                    unset($cacheItems['EffectiveDate']);
                    unset($cacheItems['InvoiceDate']);
                    $this->setSessionData(
                        'excise_order',
                        json_encode($cacheItems, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION)
                    );
                    // store response in session
                    $this->setSessionData('excise_response', $response);
                } else {
                    $this->logger->critical('Error in Excise API response');
                }
            } catch (\Zend_Http_Client_Exception $e) {
                // Catch API timeouts and network issues
                $this->logger->critical('API timeout or network issue between 
                    your store and avalara excise, please try again later.' . $e->getMessage());
                $this->response = null;
                $this->unsetSessionData('excise_response');
                $sendLog = false;
            } catch (\Exception $exp) {
                // Catch 500 exceptions
                $logContext['result'] = $exp->getMessage();
                $this->logger->critical('500 Server Error', $logContext);
                $this->response = null;
                $this->unsetSessionData('excise_response');
                $sendLog = false;
            }
        } else {
            $sendLog = false;
            $sessionResponse = $this->getSessionData('excise_response');
            if (isset($sessionResponse)) {
                $this->response = $sessionResponse;
                $this->logger->addInfo('Tax set from cache.', [
                    'request' => var_export($cartItems, true),
                    'result' => var_export($sessionResponse, true)
                ]);
            }
        }

        if ($sendLog) {
            $exeEndTime = microtime(true);
            $obj->setEventBlock("PostCalculateTax");
            $obj->setDocType("SalesOrder");
            $this->logger->logPerformance(
                $obj,
                $storeId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                ['start' => $exeStartTime, 'end' => $exeEndTime],
                ['start' => $apiStartTime, 'end' => $apiEndTime],
                __METHOD__,
                'calculateTax',
                $apiType
            );
        }

        return $this;
    }

    /**
     * Validate postcode based on country using patterns defined in
     *
     * @param string $postcode
     * @param string $countryId
     * @return bool
     */
    private function _validatePostcode($postcode, $countryId)
    {
        $postCodes = $this->postCodesConfig->getPostCodes();

        if (isset($postCodes[$countryId]) && is_array($postCodes[$countryId])) {
            $patterns = $postCodes[$countryId];

            foreach ($patterns as $pattern) {
                preg_match('/' . $pattern['pattern'] . '/', $postcode, $matches);

                if (count($matches)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get order line items
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     * @param array $lineParam
     * @param array $customerData
     * @param mixed $address
     * @return array
     */
    private function _getLineItems(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails,
        $lineParam,
        $customerData,
        $address
    ) {
        $lineCount = 0;
        $lineItems = [];
        $store = $quote->getStore();
        $items = $quote->getAllItems();

        $addressItems = [];
        if ($quote->getIsMultiShipping()) {
            foreach ($address->getAllItems() as $item) {
                if (!empty($item->getQuoteItemId())) {
                    $addressItems[] = $item->getQuoteItemId();
                }
            }
        }

        if (count($items) > 0) {
            $parentQuantities = [];
            foreach ($items as $item) {
                if ($quote->getIsMultiShipping() && !empty($addressItems)) {
                    if (!in_array($item->getId(), $addressItems)) {
                        continue;
                    }
                }
                $lineCount++;
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $item->getProduct();

                /**
                 * Avalara Original code
                 */
                /*
                    if ($item->getProductType() == "bundle") {
                        continue;
                    }
                */

                /**
                 * patch code
                 */
                if ($item->getProductType() == "bundle" || $item->getProductType() == "configurable") {
                    continue;
                }

                $itemAlternativeFuelContent = $product->getExciseAltProdContent();
                $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

                if ($item->getParentItem()) {
                    /**
                     * Avalara original code
                     */
                    /*
                        if ($item->getParentItem()->getProductType() == "configurable") {
                            continue;
                        }
                    */
                    $itemQty = $item->getParentItem()->getQty();
                } else {
                    $itemQty = $item->getQty();
                }
                
                $itemSku = $item->getSku();
                if ($item->getParentItem() != null && $item->getParentItem()->getProductType() == 'configurable') {
                    $itemUnitPrice = $item->getParentItem()->getPrice();
                } elseif ($item->getParentItem() != null && $item->getParentItem()->getProductType() == 'bundle') {
                    /* fix for resolving the issue related to bundle product fix price issue; Start */
                    if ($item->getParentItem()->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED) {
                        $itemUnitPrice = $item->getPrice();
                        try {
                            $childProducts = $this->quoteItemFactory->create()
                            ->getCollection()->addFieldToFilter(
                                "parent_item_id",
                                ['eq' => $item->getParentItem()->getId()]
                            );

                            $finalPrice = $item->getParentItem()->getPrice();
                            $simpleProductPrice = $item->getProduct()->getPrice();
                            $totalPrice = 0;
                            foreach ($childProducts as $key => $value) {
                                $totalPrice = $totalPrice + $value->getProduct()->getPrice();
                            }
                            $itemUnitPrice = ($finalPrice/$totalPrice) * $simpleProductPrice;
                        } catch (\Exception $e) {
                            $this->logger->critical("bundle product fix price >>".$e->getMessage());
                        }
                    } else {
                        $itemUnitPrice = $item->getPrice();
                    }
                    /* -- End -- */
                } else {
                    $itemUnitPrice = $item->getPrice();
                }
                //$itemUnitPrice = $item->getRowTotal();
                $itemAlternateUnitPrice = $item->getProduct()->getExcisePurchaseUnitPrice();
                $itemUnitOfMeasure = ($item->getProduct()
                    ->getAttributeText('excise_unit_of_measure')) ? $item->getProduct()
                    ->getAttributeText('excise_unit_of_measure') : "PAK";
                if ($item->getProductType() == BundleProductType::TYPE_CODE) {
                    $itemSku = $product->getData('sku');
                }
                $productCode = $itemSku;

                $lineitems = [
                    'InvoiceLine' => $item->getId(),
                    'ProductCode' => $productCode,
                    'UnitPrice' => $itemUnitPrice,
                    'BilledUnits' => $itemQty,
                    'LineAmount' => ($item->getParentItem() != null && $item->getParentItem()
                        ->getProductType() == 'configurable') ? ($item->getParentItem()
                        ->getRowTotal() - $item->getDiscountAmount()) : ($item->getRowTotal() - $item->getDiscountAmount()),
                    'AlternateUnitPrice' => $itemAlternateUnitPrice,
                    'AlternateLineAmount' => $itemAlternateUnitPrice * $itemQty,
                    'Currency' => $currencyCode,
                    'UnitOfMeasure' => $itemUnitOfMeasure,
                    'TaxIncluded' => $this->exciseTaxConfig->getPriceIncludesTax($quote->getStoreId()),
                    'AlternativeFuelContent' => $itemAlternativeFuelContent,
                    'UserData' => '',
                    'CustomString1' => $itemSku,
                    'DestinationType' => '',
                    'Destination' => '',
                    'DestinationOutCityLimitInd' => 'N',
                    'DestinationSpecialJurisdictionInd' => 'N',
                    'DestinationExciseWarehouse' => null,
                ];
                // To set common data elements
                $transactionItem = array_merge($lineitems, $lineParam); //@codingStandardsIgnoreLine
                array_push($lineItems, $transactionItem);
            }

            $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
            $shippingLineItems = $this->_getShippingLineItems(
                $lineitems,
                $quote,
                $lineCount,
                $customerData,
                $shippingAmount,
                true,
                $entityType = ''
            );
            $shippingLineItems = array_merge($shippingLineItems, $lineParam);
            array_push($lineItems, $shippingLineItems);
        }
        return $lineItems;
    }

    /**
     * Verify if the order changed compared to session
     *
     * @param  array $currentOrder
     * @return bool
     */
    private function orderChanged($currentOrder)
    {
        $sessionResponse = $this->getSessionData('excise_response');
        if (!isset($sessionResponse)) {
            return true;
        }
        $sessionOrder = $this->getSessionData('excise_order');
        unset($currentOrder['EffectiveDate']);
        unset($currentOrder['InvoiceDate']);
        $currentOrder = json_encode($currentOrder, JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION);
        if ($sessionOrder) {
            return $currentOrder !== $sessionOrder;
        } else {
            return true;
        }
    }

    /**
     * Get prefixed session data from checkout/session
     *
     * @param  string $key
     * @return object
     */
    private function getSessionData($key)
    {
        return $this->checkoutSession->getData($this->getKeyPrefix() . $key);
    }

    /**
     * Set prefixed session data in checkout/session
     *
     * @param  string $key
     * @param  string $val
     * @return object
     */
    private function setSessionData($key, $val)
    {
        return $this->checkoutSession->setData($this->getKeyPrefix() . $key, $val);
    }

    /**
     * Unset prefixed session data in checkout/session
     *
     * @param  string $key
     * @return object
     */
    private function unsetSessionData($key)
    {
        return $this->checkoutSession->unsetData($this->getKeyPrefix() . $key);
    }

    /**
     * Prefix for the cache key
     *
     * @return string
     */
    private function getKeyPrefix()
    {
        return 'avalara_excise_' . $this->dateTime->date()->format('d') . '_';
    }
}
