<?php

namespace HookahShisha\Avalara\Model;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Directory\Model\Country\Postcode\ConfigInterface;
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
use Magento\Tax\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\OrderRepository;
use Avalara\Excise\Model\ProcessTaxQuote as AvalaraProcessTaxQuote;

/**
 * Avalara Tax
 */
class ProcessTaxQuote extends AvalaraProcessTaxQuote
{
    /**
     * Value for invoice transaction
     */
    const SALE_INVOICE_TYPE = "INVOICE";

    /**
     * Value for refund transaction
     */
    const SALE_REFUND_TYPE = "REFUND";

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
        OrderRepository $orderRepository
    ) {
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
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->postCodesConfig = $postCodesConfig;
        $this->shipLineNo = Constants::SHIP_LINE_NO;
        $this->dateTime = $exciseTaxConfig->getTimeZoneObject();
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->mathRandom = $mathRandom;
        $this->exciseClient = $exciseClient;
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
                // Catch API timeouts and network issues
                $this->logger->critical('API timeout or network issue between
                    your store and avalara excise, please try again later.' . $e->getMessage());
                $this->response = null;
                $this->unsetSessionData('excise_response');
                $sendLog = false;
            } catch (\Exception $exp) {
                // Catch 500 exceptions
                $this->logger->critical('Server Error: ' . $exp->getMessage());
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
                $itemUnitOfMeasure = $product->getAttributeText('excise_unit_of_measure');
                $itemUnitQuantity = $product->getExciseUnitQuantity();
                if (in_array(
                    $item->getProductType(),
                    [BundleProductType::TYPE_CODE, Configurable::TYPE_CODE]
                )) {
                    $itemSku = $product->getData('sku');
                    $itemName = $product->getData('name');
                }
                $productCode = $itemSku;
                $itemUnitVolume = $product->getExciseUnitVolume();
                $lineitems = [
                    'InvoiceLine' => $lineCount,
                    'ProductCode' => $productCode,
                    'UnitPrice' => $itemUnitPrice,
                    'BilledUnits' => $itemQty,
                    'LineAmount' => $lineAmount,
                    //'LineAmount' => $item->getRowTotal(),
                    'AlternateUnitPrice' => $itemAlternateUnitPrice,
                    'AlternateLineAmount' => $itemAlternateUnitPrice * $itemQty,
                    'Currency' => $currencyCode,
                    'UnitOfMeasure' => $itemUnitOfMeasure,
                    'TaxIncluded' => $this->exciseTaxConfig->getPriceIncludesTax($queueObject->getStoreId()),
                    'AlternativeFuelContent' => $itemAlternativeFuelContent,
                    'UnitQuantity' => $itemUnitQuantity,
                    'UserData' => '',
                    'CustomString1' => $itemSku . "-" . $itemName,
                    'CustomString2' => $queueObject->getOrder()->getIncrementId(),
                    'CustomString3' => $queueObject->getIncrementId(),
                    'DestinationType' => '',
                    'Destination' => '',
                    'DestinationOutCityLimitInd' => 'N',
                    'DestinationSpecialJurisdictionInd' => 'N',
                    'DestinationExciseWarehouse' => null,
                    'UnitVolume' => $itemUnitVolume,
                    'SaleType' => $saleType
                ];
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
            'UnitQuantity' => "1",
            'UnitQuantityUnitOfMeasure' => "",
            'UserData' => $customerData['UserData'],
            'CustomString1' => "",
            'DestinationType' => '',
            'Destination' => '',
            'DestinationOutCityLimitInd' => 'N',
            'DestinationSpecialJurisdictionInd' => 'N',
            'DestinationExciseWarehouse' => null,
            'UnitVolume' => "",
            'UnitVolumeUnitOfMeasure' => "",
            'SaleType' => $saleType
        ];
        return $lineitems;
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
            foreach ($items as $item) {
                if ($quote->getIsMultiShipping() && !empty($addressItems)) {
                    if (!in_array($item->getId(), $addressItems)) {
                        continue;
                    }
                }
                $lineCount++;
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_productRepository->get($item->getSku());

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
                $itemAlternateUnitPrice = $product->getExcisePurchaseUnitPrice();
                $itemUnitOfMeasure = $product->getAttributeText('excise_unit_of_measure');
                $itemUnitQuantity = $product->getExciseUnitQuantity();
                if ($item->getProductType() == BundleProductType::TYPE_CODE) {
                    $itemSku = $product->getData('sku');
                }
                $productCode = $itemSku;
                $itemUnitVolume = $product->getExciseUnitVolume();

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
                    'UnitQuantity' => $itemUnitQuantity,
                    'UserData' => '',
                    'CustomString1' => $itemSku,
                    'DestinationType' => '',
                    'Destination' => '',
                    'DestinationOutCityLimitInd' => 'N',
                    'DestinationSpecialJurisdictionInd' => 'N',
                    'DestinationExciseWarehouse' => null,
                    'UnitVolume' => $itemUnitVolume,
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
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    public function getDestinationLocation($address)
    {
        $destination = [
            'DestinationCountryCode' => $address->getCountry(),
            'DestinationPostalCode' => $address->getPostcode(),
            'DestinationJurisdiction' => $address->getRegionCode(),
            'DestinationCity' => $address->getCity(),
            'DestinationCounty' => $address->getCounty() !== null ? $address->getCounty() : '',
            'DestinationAddress1' => substr($address->getStreetLine(1), 0, 35)
        ];

        return $destination;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    public function getDestinationLocationForInvoiceAndCM($address)
    {
        $destination = [
            'DestinationCountryCode' => $address->getCountryId(),
            'DestinationPostalCode' => $address->getPostcode(),
            'DestinationJurisdiction' => $address->getRegionCode(),
            'DestinationCity' => $address->getCity(),
            'DestinationCounty' => $address->getCounty() !== null ? $address->getCounty() : '',
            'DestinationAddress1' => substr($address->getStreetLine(1), 0, 35)
        ];

        return $destination;
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
