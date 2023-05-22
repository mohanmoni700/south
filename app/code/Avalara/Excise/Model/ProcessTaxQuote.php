<?php

namespace Avalara\Excise\Model;

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
use Magento\Sales\Model\OrderRepository;
use Avalara\Excise\Logger\ExciseLogger;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class ProcessTaxQuote
{
    /**
     * Value for invoice transaction
     */
    const SALE_INVOICE_TYPE = "INVOICE";

    /**
     * Value for refund transaction
     */
    const SALE_REFUND_TYPE = "REFUND";

    /**
     * Value for API pass 
     */
    const APIPASS = "true";

    /**
     * Value for API retursns response as error 
     */
    const APIERROR = "apierror";

     /**
     * Value for API failed 
     */
    const APIFAIL = "false";

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
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param RegionFactory $regionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ExciseClient $exciseClient
     * @param ExciseClientFactory $exciseClientFactory
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig
     * @param LoggerInterface $logger
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\OrderRepository $orderRepository 
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        RegionFactory $regionFactory,
        ScopeConfigInterface $scopeConfig,
        ExciseClient $exciseClient,
        ExciseClientFactory $exciseClientFactory,
        ProductMetadataInterface $productMetadata,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig,
        LoggerInterface $logger,
        ExciseTaxConfig $exciseTaxConfig,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Math\Random $mathRandom,
        OrderRepository $orderRepository,
        ExciseLogger $loggerapi
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
        $this->loggerapi = $loggerapi;
    }
    /**
     *
     * Tax calculation for Invoice or Creditmemo
     * @return $this
     */
    public function getRecalculatedTax($queueObject, $entityType)
    {

        $obj = $this->exciseClient->getDataObject();
        $exeEndTime = $apiStartTime = $apiEndTime = 0;
        $exeStartTime = microtime(true);
        $apiType = Constants::EXCISE_API;
        $cartItems = $details = $customerData = [];

        if ($queueObject->getIncrementId() && $queueObject->hasData('excise_tax_response_order')) {
            $exciseTaxResponseOrder = $queueObject->getData('excise_tax_response_order');
            $exciseTaxResponseOrderData = json_decode((string)$exciseTaxResponseOrder, true);
            if (!empty($exciseTaxResponseOrderData['Status']) && $exciseTaxResponseOrderData['Status'] == "Success") {
                return self::APIPASS;
            }
        }

        $address = $queueObject->getShippingAddress();
        $storeId = $queueObject->getStoreId();

        if (empty($address)) {
            // virtual order
            $address = $queueObject->getBillingAddress();
        }

        $accountNo = $this->exciseTaxConfig->getAvaTaxAccountNumber($storeId);
        $licenseKey = $this->exciseTaxConfig->getAvaTaxLicenseKey($storeId);
        $companyId = $this->exciseTaxConfig->getExciseCompanyId($storeId);

        if (!$accountNo || !$licenseKey || !$companyId) {
            $details['account_number'] = $accountNo;
            $details['licenseKey'] = $licenseKey;
            $details['companyId'] = $companyId;
            $this->logger->critical('Invalid excise tax credentials: ' . json_encode($details) . ' post');
            return;
        }

        if (!$address->getPostcode()) {
            return;
        }

        $originLocation = $this->getOriginLocation($queueObject);

        $destinationLocation = $this->getDestinationLocationForInvoiceAndCM($address);

        $saleCountry = $this->getSaleCountry($queueObject);

        $customerData['UserData'] = $address->getFirstname() . " " . $address->getLastname();

        $lineParam = array_merge($originLocation, $destinationLocation, $saleCountry, $customerData);
        $lineItems = $this->_getQuoteObjectLineItems($queueObject, $lineParam, $entityType, $customerData);
        $obj->setLineCount(count($lineItems));

        $cartItems['TransactionLines'] = $lineItems;
        $cartItems['EffectiveDate'] = $queueObject->getUpdatedAt();
        $cartItems['InvoiceDate'] = $queueObject->getUpdatedAt();

        if ($entityType == "invoice") {
            /* This can be used for further release based on API changes.*/
            $cartItems['InvoiceNumber'] = "INV" . $queueObject->getIncrementId();
            $cartItems['UserTranId'] = "INV" . $queueObject->getIncrementId(). '-' . random_int(10000000,90000000000);
            $cartItems['AvaTaxTransactionType'] = "SalesInvoice";
            $obj->setEventBlock("InvoicePostCalculateTax");
            $obj->setDocType(self::SALE_INVOICE_TYPE);
            $obj->setDocCode($cartItems['InvoiceNumber']);
        } elseif ($entityType == "creditmemo") {
            /* This can be used for further release based on API changes.*/
            $cartItems['InvoiceNumber'] = "CM" . $queueObject->getIncrementId();
            $cartItems['UserTranId'] = "CM" . $queueObject->getIncrementId(). '-' . random_int(10000000,90000000000);
            $cartItems['AvaTaxTransactionType'] = "ReturnInvoice";
            $obj->setEventBlock("CreditMemoPostCalculateTax");
            $obj->setDocType(self::SALE_REFUND_TYPE);
            $obj->setDocCode($cartItems['InvoiceNumber']);
        }

        $order = $this->orderRepository->get($queueObject->getOrderId());        
        $cartItems['TitleTransferCode'] = 'DEST';
        $cartItems['TransactionType'] = $this->getCustomerType($order);
        $cartItems['Seller'] = $this->storeManager->getStore()->getName();
        $randNumber = $this->mathRandom->getRandomNumber(0, 9999);
        $custId = $order->getCustomerId() ? $order->getCustomerId() : "Gust-".$randNumber;
        $cartItems['Buyer'] = $custId;
        $cartItems['UserData'] = $customerData['UserData'];

        $cartItems['SourceSystem'] = Constants::SOURCE_SYSTEM;
        $cartItems['CommitStatus'] = "SAVED";
        $cartItems['EntityUseCode'] = $this->getEntityUseCode($order);

        try {
            /** @var $exciseClient ExciseClient */
            $exciseClient = $this->exciseClientFactory->create();
            $this->response = null;
            $apiStartTime = microtime(true);
            $response = $exciseClient->createExciseTaxTransaction($storeId, $cartItems);
            $apiEndTime = microtime(true);
            $this->response = $response;

            $queueObject->setExciseTaxResponseOrder(json_encode($this->response));
            $queueObject->save();

            $exeEndTime = microtime(true);
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

            if ($this->response['Status'] == "Success") {
                return self::APIPASS;
            } elseif($this->response['Status'] == "Errors found"){
                return self::APIERROR;
            }else{
                return self::APIFAIL;
            }
        } catch (\Zend_Http_Client_Exception $e) {
            // code to add CEP logs for exception
            try {    
                $functionName = "getRecalculatedTax";
                $operationName = "Framework_Interaction_Rest_Transaction_Create";
                $source = "avatax_create_transaction";
                // @codeCoverageIgnoreStart
                $this->loggerapi->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e,
                    $source,
                    $storeId
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            // Catch API timeouts and network issues
            $this->logger->critical(
                'API timeout or network issue between your store and avalara excise, please try again later.' .
                    ' error'
            );
            $this->response = null;
        }
        return self::APIFAIL;
    }

    /**
     * Get order line items
     *
     * @return array
     */
    private function _getQuoteObjectLineItems($queueObject, $lineParam, $entityType, $customerData)
    {
        $lineItems = [];

        $orderItems = $queueObject->getOrder()->getAllItems();
        $orderItemsArray = [];
        foreach ($orderItems as $orderItem) {
            $orderItemsArray[$orderItem->getItemId()] = $orderItem;
        }

        $items = $queueObject->getItems();

        if (count($items) > 0) {
            $lineCount = 0;
            foreach ($items as $item) {
                $saleType = self::SALE_INVOICE_TYPE;
                $product = $this->_productRepository->getById($item->getProductId());

                if ($product->getTypeId() == "bundle") {
                    continue;
                }

                $invoiceOrderItem = $orderItemsArray[$item->getOrderItemId()];
                if ($invoiceOrderItem->getParentItem()) {
                    if ($invoiceOrderItem->getParentItem()->getProductType() == "configurable") {
                        continue;
                    }
                }

                $lineCount++;

                $itemAlternativeFuelContent = $product->getExciseAltProdContent();
                $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
                $itemQty = $item->getQty();
                $itemSku = $item->getSku();
                $itemName = $item->getName();
                $itemUnitPrice = $item->getPrice();
                $lineAmount = ($itemUnitPrice * $itemQty) - $item->getDiscountAmount();
                if ($entityType == "creditmemo") {
                    $saleType = self::SALE_REFUND_TYPE;
                    $itemQty = $itemQty * -1;
                    $lineAmount = $lineAmount * -1;
                }
                $itemAlternateUnitPrice = $product->getExcisePurchaseUnitPrice();
                $itemAlternateUnitPrice1 = $product->hasData('excise_alternate_price_1') ? (float)$product->getData('excise_alternate_price_1') : '';
                $itemAlternateUnitPrice2 = $product->hasData('excise_alternate_price_2') ? (float)$product->getData('excise_alternate_price_2') : '';
                $itemAlternateUnitPrice3 = $product->hasData('excise_alternate_price_3') ? (float)$product->getData('excise_alternate_price_3') : '';
                $itemUnitOfMeasure = ($product->getAttributeText('excise_unit_of_measure')) ? $product->getAttributeText('excise_unit_of_measure') : "PAK";
                if (in_array(
                    $item->getProductType(),
                    [BundleProductType::TYPE_CODE, Configurable::TYPE_CODE]
                )) {
                    $itemSku = $product->getData('sku');
                    $itemName = $product->getData('name');
                }
                $productCode = $itemSku;
                $lineitems = [
                    'InvoiceLine' => $lineCount,
                    'ProductCode' => $productCode,
                    'UnitPrice' => $itemUnitPrice,
                    'BilledUnits' => $itemQty,
                    'LineAmount' => $lineAmount,
                    'AlternateUnitPrice' => $itemAlternateUnitPrice,
                    'AlternateLineAmount' => $itemAlternateUnitPrice * $itemQty,
                    'Currency' => $currencyCode,
                    'UnitOfMeasure' => $itemUnitOfMeasure,
                    'TaxIncluded' => $this->exciseTaxConfig->getPriceIncludesTax($queueObject->getStoreId()),
                    'AlternativeFuelContent' => $itemAlternativeFuelContent,
                    'UserData' => '',
                    'CustomString1' => $itemSku . "-" . $itemName,
                    'CustomString2' => $queueObject->getOrder()->getIncrementId(),
                    'CustomString3' => $queueObject->getIncrementId(),
                    'DestinationType' => '',
                    'Destination' => '',
                    'DestinationOutCityLimitInd' => 'N',
                    'DestinationSpecialJurisdictionInd' => 'N',
                    'DestinationExciseWarehouse' => null,
                    'SaleType' => $saleType
                ];
                if ($itemAlternateUnitPrice1 != '') {
                    $lineitems['CustomNumeric1'] = $itemAlternateUnitPrice1;
                }
                if ($itemAlternateUnitPrice2 != '') {
                    $lineitems['CustomNumeric2'] = $itemAlternateUnitPrice2;
                }
                if ($itemAlternateUnitPrice3 != '') {
                    $lineitems['CustomNumeric3'] = $itemAlternateUnitPrice3;
                }
                // To set common data elements
                $transactionItem = array_merge($lineitems, $lineParam); //@codingStandardsIgnoreLine
                array_push($lineItems, $transactionItem);
            }
            $shippingAmount = $queueObject->getShippingAmount();
            $shippingLineItems = $this->_getShippingLineItems(
                $lineitems,
                $queueObject,
                $lineCount,
                $customerData,
                $shippingAmount,
                false,
                $entityType
            );
            $shippingLineItems = array_merge($shippingLineItems, $lineParam);
            array_push($lineItems, $shippingLineItems);
        }
        return $lineItems;
    }

    /**
     * @param array $lineitems
     * @param Invoice | Creditmemo | Quote $queueObject
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
        }elseif($entityType == "invoice"){
            $saleType = self::SALE_INVOICE_TYPE;
        }else{
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
        $cartItems['AvaTaxTransactionType'] = "SalesOrder";
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
                $quote->save();

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
                // code to add CEP logs for exception
                try {
                    $functionName = "getTaxForOrder";
                    $operationName = "Framework_Interaction_Rest_Transaction_Create";
                    $source = "avatax_create_transaction";
                    // @codeCoverageIgnoreStart
                    $this->logger->logDebugMessage(
                        $functionName,
                        $operationName,
                        $e,
                        $source,
                        $storeId
                    );
                    // @codeCoverageIgnoreEnd
                } catch (\Exception $e) {
                    //do nothing
                }
                // end of code to add CEP logs for exception
                // Catch API timeouts and network issues
                $this->logger->critical('API timeout or network issue between 
                    your store and avalara excise, please try again later.' . $e->getMessage());
                $this->response = null;
                $this->unsetSessionData('excise_response');
                $sendLog = false;
            } catch (\Exception $exp) {
                // code to add CEP logs for exception
                try {
                    $functionName = "getTaxForOrder";
                    $operationName = "Framework_Interaction_Rest_Transaction_Create";
                    $source = "avatax_create_transaction";
                    // @codeCoverageIgnoreStart
                    $this->logger->logDebugMessage(
                        $functionName,
                        $operationName,
                        $exp,
                        $source,
                        $storeId
                    );
                    // @codeCoverageIgnoreEnd
                } catch (\Exception $e) {
                    //do nothing
                }
                // end of code to add CEP logs for exception

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
                $this->logger->info('Tax set from cache.', [
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
     * Get the API response
     *
     * @return array
     */
    public function getResponse()
    {
        if ($this->response) {
            return [
                'body' => $this->response,
                'status' => $this->response['Status'],
            ];
        } else {
            return [
                'status' => 204,
            ];
        }
    }

    public function getOriginLocation($quote)
    {
        $countryId = $this->scopeConfig->getValue(
            'shipping/origin/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $postalcode = $this->scopeConfig->getValue(
            'shipping/origin/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $shippingRegionId = $this->scopeConfig->getValue(
            'shipping/origin/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $regionId = $this->regionFactory->create()->load($shippingRegionId)->getCode();

        $city = $this->scopeConfig->getValue(
            'shipping/origin/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $street1 = $this->scopeConfig->getValue(
            'shipping/origin/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $origin = [
            'OriginCountryCode' => $countryId,
            'OriginPostalCode' => $postalcode,
            'OriginJurisdiction' => $regionId,
            'OriginCity' => $city,
            'OriginAddress1' => $street1,
        ];

        return $origin;
    }

    public function getDestinationLocation($address)
    {
        $destination = [
            'DestinationCountryCode' => $address->getCountry(),
            'DestinationPostalCode' => $address->getPostcode(),
            'DestinationJurisdiction' => $address->getRegionCode(),
            'DestinationCity' => $address->getCity(),
            'DestinationCounty' => $address->getCounty() !== null ? $address->getCounty() : '',
            'DestinationAddress1' => $address->getStreetLine(1)
        ];

        return $destination;
    }

    public function getDestinationLocationForInvoiceAndCM($address)
    {
        $destination = [
            'DestinationCountryCode' => $address->getCountryId(),
            'DestinationPostalCode' => $address->getPostcode(),
            'DestinationJurisdiction' => $address->getRegionCode(),
            'DestinationCity' => $address->getCity(),
            'DestinationCounty' => $address->getCounty() !== null ? $address->getCounty() : '',
            'DestinationAddress1' => $address->getStreetLine(1)
        ];

        return $destination;
    }

    public function getSaleCountry($quote)
    {
        $countryId = $this->scopeConfig->getValue(
            'shipping/origin/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $postalcode = $this->scopeConfig->getValue(
            'shipping/origin/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $shippingRegionId = $this->scopeConfig->getValue(
            'shipping/origin/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $regionId = $this->regionFactory->create()->load($shippingRegionId)->getCode();

        $city = $this->scopeConfig->getValue(
            'shipping/origin/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        $street1 = $this->scopeConfig->getValue(
            'shipping/origin/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        /* This can be used for further release based on API changes.
        $saleCountry = [
            'SaleCountryCode' => $countryId,
            'SalePostalCode' => $postalcode,
            'SaleJurisdiction' => $regionId,
            'SaleCity' => $city,
            'SaleAddress1' => $street1,
        ]; */

        $saleCountry = [
            'SaleCountryCode' => '',
            'SalePostalCode' => '',
            'SaleJurisdiction' => '',
            'SaleCity' => '',
            'SaleAddress1' => '',
        ];

        return $saleCountry;
    }

    public function getCustomerData($quote)
    {
        $name = '';
        if ($quote->getCustomerIsGuest()) {
            $name = $quote->getCustomerFirstname() . " " . $quote->getCustomerLastname();
        }

        $customer['UserData'] = $name;
        return $customer;
    }

    /**
     * Fetch entity use code
     * If customer is guest then send blank.
     * If customer is logged in send from his profile set by admin.
     *
     * @param   object  $quote  Current Quote
     *
     * @return  string
     */
    public function getEntityUseCode($quote)
    {
        $entityUseCode = "NONE";
        if ($quote->getCustomerIsGuest() || empty($quote->getCustomerId())) {
            return $entityUseCode;
        }
        $customer = $this->customerRepository->getById($quote->getCustomerId());
        if ($customer->getCustomAttribute('entity_use_code') && !empty($customer->getCustomAttribute('entity_use_code')->getValue())) {
            return $customer->getCustomAttribute('entity_use_code')->getValue(); 
        }
        return $entityUseCode;
    }

    /**
     * Fetch customer type or transaction type code.
     * If customer is guest send value from config.
     * If customer is logged in and value not set send from config.
     * 
     * @param   object  $quote  Current Quote
     *
     * @return  string
     */
    public function getCustomerType($quote)
    {
        $transactionType = $this->scopeConfig->getValue(
                                Constants::CUSTOMER_TYPE_PATH,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $quote->getStoreId()
                            );
        if ($quote->getCustomerIsGuest() || empty($quote->getCustomerId())) {
            return $transactionType;
        }
        $customer = $this->customerRepository->getById($quote->getCustomerId());
        if ($customer->getCustomAttribute('customer_type') && !empty($customer->getCustomAttribute('customer_type')->getValue())) {
            return $customer->getCustomAttribute('customer_type')->getValue(); 
        }
        //return default customer type when customer is logged in
        return Constants::CUSTOMER_TYPE_DEFAULT;
    }

    /**
     * Get a specific line item breakdown from the API response
     *
     * @param int $id
     * @return array
     */
    public function getResponseLineItem($id)
    {
        $taxlineitems = [];
        if ($this->response) {
            $responseBody = $this->response;

            if ($responseBody['Status'] == 'Success' && isset($responseBody['TransactionTaxes'])) {
                $lineItems = $responseBody['TransactionTaxes'];

                foreach ($lineItems as $lineItem) {
                    if ($lineItem['InvoiceLine'] == $id) {
                        $taxlineitems[] = $lineItem;
                    }
                }
            }
        }

        return $taxlineitems;
    }

    /**
     * Get the shipping breakdown from the API response
     *
     * @return array
     */
    public function getResponseShipping()
    {
        $taxlineitems = [];
        if ($this->response) {
            $responseBody = $this->response;
            if ($responseBody['Status'] == 'Success' && isset($responseBody['TransactionTaxes'])) {
                $lineItems = $responseBody['TransactionTaxes'];

                foreach ($lineItems as $lineItem) {
                    if ($lineItem['InvoiceLine'] == $this->shipLineNo) {
                        $taxlineitems[] = $lineItem;
                    }
                }
            }
        }
        return $taxlineitems;
    }

    /**
     * Determine if valid response
     *
     * @return bool
     */
    public function isValidResponse()
    {
        $response = $this->getResponse();

        if (isset($response['body']['TotalTaxAmount']) && $response['body']['Status'] == "Success") {
            return true;
        }

        return false;
    }

    /**
     * Validate postcode based on country using patterns defined in
     * app/code/Magento/Directory/etc/zip_codes.xml
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

                if ($item->getProductType() == "bundle") {
                    continue;
                }

                $itemAlternativeFuelContent = $product->getExciseAltProdContent();
                $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

                if ($item->getParentItem()) {
                    if ($item->getParentItem()->getProductType() == "configurable") {
                        continue;
                    }
                    $itemQty = $item->getParentItem()->getQty();
                } else {
                    $itemQty = $item->getQty();
                }

                $itemSku = $item->getSku();
                $itemUnitPrice = $item->getPrice();
                //$itemUnitPrice = $item->getRowTotal();
                $itemAlternateUnitPrice = $item->getProduct()->getExcisePurchaseUnitPrice();
                $itemUnitOfMeasure = ($item->getProduct()->getAttributeText('excise_unit_of_measure')) ? $item->getProduct()->getAttributeText('excise_unit_of_measure') : "PAK";
                if ($item->getProductType() == BundleProductType::TYPE_CODE) {
                    $itemSku = $product->getData('sku');
                }
                $productCode = $itemSku;

                $lineitems = [
                    'InvoiceLine' => $item->getId(),
                    'ProductCode' => $productCode,
                    'UnitPrice' => $itemUnitPrice,
                    'BilledUnits' => $itemQty,
                    'LineAmount' => $item->getRowTotal() - $item->getDiscountAmount(),
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
                $itemAlternateUnitPrice1 = $item->getProduct()->hasData('excise_alternate_price_1') ? (float)$item->getProduct()->getData('excise_alternate_price_1') : '';
                $itemAlternateUnitPrice2 = $item->getProduct()->hasData('excise_alternate_price_2') ? (float)$item->getProduct()->getData('excise_alternate_price_2') : '';
                $itemAlternateUnitPrice3 = $item->getProduct()->hasData('excise_alternate_price_3') ? (float)$item->getProduct()->getData('excise_alternate_price_3') : '';
                if ($itemAlternateUnitPrice1 != '') {
                    $lineitems['CustomNumeric1'] = $itemAlternateUnitPrice1;
                }
                if ($itemAlternateUnitPrice2 != '') {
                    $lineitems['CustomNumeric2'] = $itemAlternateUnitPrice2;
                }
                if ($itemAlternateUnitPrice3 != '') {
                    $lineitems['CustomNumeric3'] = $itemAlternateUnitPrice3;
                }
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
