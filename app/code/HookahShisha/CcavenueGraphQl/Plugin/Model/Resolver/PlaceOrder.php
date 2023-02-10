<?php

namespace HookahShisha\CcavenueGraphQl\Plugin\Model\Resolver;

use HookahShisha\CcavenueGraphQl\Helper\Data;
use HookahShisha\CcavenueGraphQl\Logger\Logger;
use Magento\Directory\Model\Country;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This plugin validates and saves the order attribute
 */
class PlaceOrder
{
    /**
     * @var OrderInterfaceFactory
     */
    private OrderInterfaceFactory $orderFactory;

    /**
     * @var Logger
     */
    private Logger $ccavLogger;


    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Country
     */
    private Country $countryFactory;

    const CC_AVENUE_CODE = 'ccavenue';

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param Logger $ccavLogger
     * @param Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Country $countryFactory
     */
    public function __construct(
        OrderInterfaceFactory   $orderFactory,
        Logger $ccavLogger,
        Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Country $countryFactory
    )
    {
        $this->orderFactory = $orderFactory;
        $this->ccavLogger = $ccavLogger;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterResolve(
        MagentoPlaceOrder $subject, // NOSONAR
                          $return,
        Field             $field, // NOSONAR
                          $context, // NOSONAR
        ResolveInfo       $info, // NOSONAR
        array             $value = null, // NOSONAR
        array             $args = null // NOSONAR
    ): array
    {
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($return['order']['order_number'] ?? '');
        $this->ccavLogger->info('Order Id : '.$order->getIncrementId());
        $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();

        if($paymentMethodCode === self::CC_AVENUE_CODE ) {
            $return['order']['ccavenue_iframe_url'] = $this->getCcavenueUrl($order);
        }

        return $return;
    }

    /**
     * Get order data using order increment id.
     *
     * @param Order $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCcavenueUrl(Order $order): string
    {
        $ccaOrderData = [];

        $ccaOrderData['merchant_id'] = $this->getCcavenueConfigData('merchant_id');
        $redirectUrl = $this->dataHelper->getCCaveCusData('graphql_ccavenue/general_Setting/redirect_url');
        $cancelUrl = $this->dataHelper->getCCaveCusData('graphql_ccavenue/general_Setting/cancel_url');
        $transitUrl = $this->dataHelper->getCCaveCusData('graphql_ccavenue/general_Setting/ccavenue_en_url');

        $this->ccavLogger->info('Redirect url: '. $redirectUrl);
        $this->ccavLogger->info('Cancel url: '. $cancelUrl);
        $this->ccavLogger->info('Trans url: '. $transitUrl);

        $billing_address = $order->getBillingAddress();
        if (!empty($billing_address)) {
            $ccaOrderData['billing_name']    = $billing_address->getFirstname().' '.$billing_address->getLastname();
            $ccaOrderData['billing_address'] = $billing_address->getStreetLine(1);
            $ccaOrderData['billing_city']    = $billing_address->getCity();
            $ccaOrderData['billing_state']   = $billing_address->getRegion();
            $ccaOrderData['billing_zip']     = $billing_address->getPostcode();
            $ccaOrderData['billing_country'] = $this->getBillingCountryName($billing_address);
            $ccaOrderData['billing_tel']     = $billing_address->getTelephone();
            $ccaOrderData['billing_email']   = $order->getCustomerEmail();
        }

        $shipping_address = $order->getShippingAddress();
        if (!empty($shipping_address)) {
            $ccaOrderData['delivery_name']    = $shipping_address->getFirstname().' '.$shipping_address->getLastname();
            $ccaOrderData['delivery_address'] = $shipping_address->getStreetLine(1);
            $ccaOrderData['delivery_city']    = $shipping_address->getCity();
            $ccaOrderData['delivery_state']   = $shipping_address->getRegion();
            $ccaOrderData['delivery_zip']     = $shipping_address->getPostcode();
            $ccaOrderData['delivery_country'] = $this->getShippingCountryName($shipping_address);
            $ccaOrderData['delivery_tel']     = $shipping_address->getTelephone();
        } else {
            $ccaOrderData['delivery_name']    = $billing_address->getFirstname().' '.$billing_address->getLastname();
            $ccaOrderData['delivery_address'] = $billing_address->getStreetLine(1);
            $ccaOrderData['delivery_city']    = $billing_address->getCity();
            $ccaOrderData['delivery_state']   = $billing_address->getRegion();
            $ccaOrderData['delivery_zip']     = $billing_address->getPostcode();
            $ccaOrderData['delivery_country'] = $this->getBillingCountryName($billing_address);
            $ccaOrderData['delivery_tel']     = $billing_address->getTelephone();
        }

        $ccaOrderData['order_id']        = $order->getIncrementId();
        $ccaOrderData['amount']          = round($order->getGrandTotal(), 2);
        $ccaOrderData['currency']        = $order->getOrderCurrencyCode();
        $ccaOrderData['redirect_url']    = $redirectUrl;
        $ccaOrderData['cancel_url']      = $cancelUrl;
        $ccaOrderData['trans_url']       = $transitUrl;
        $ccaOrderData['language']        = 'EN';
        $ccaOrderData['tid']             = time();
        $ccaOrderData['merchant_param1'] = $order->getIncrementId();
        $ccaOrderData['merchant_param2'] = $order->getId();

        if ($this->getCcavenueConfigData('integration_type') == 'iframe') {
            $ccaOrderData['integration_type'] = 'iframe_normal';
        }

        $merchant_data = http_build_query($ccaOrderData);

        $this->ccavLogger->info('Merchant data : '.$merchant_data);

        $encryption_key = $this->getCcavenueConfigData('encryption_key');

        return 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction&integration_type=iframe&access_code=' .
            $this->getCcavenueConfigData('access_code') .
            '&encRequest='.$this->dataHelper->encrypt($merchant_data, $encryption_key);

    }//end ccavenueData()

    /**
     * Get shipping country name
     *
     * @param OrderAddressInterface $shipping_address
     * @return string
     */
    public function getShippingCountryName(OrderAddressInterface $shipping_address): string
    {
        return $this->countryFactory->loadByCode($shipping_address->getCountryId())->getName();
    }//end getShippingCountryName()

    /**
     * Get billing country name
     *
     * @param OrderAddressInterface $billing_address
     * @return string
     */
    public function getBillingCountryName(OrderAddressInterface $billing_address): string
    {
        return $this->countryFactory->loadByCode($billing_address->getCountryId())->getName();
    }//end getBillingCountryName()

    /**
     * Return url according to environment
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCgiUrl()
    {
        $env = $this->getCcavenueConfigData('environment');
        if ($env === 'production') {
            return $this->getCcavenueConfigData('production_url');
        }

        return $this->getCcavenueConfigData('sandbox_url');
    }//end getCgiUrl()

    /**
     * Get config setting data related to ccavenue.
     *
     * @param string $field
     * @throws NoSuchEntityException
     */
    public function getCcavenueConfigData($field)
    {
        $path    = 'payment/ccavenue/'.$field;
        $storeid = $this->storeManager->getStore()->getStoreId();
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeid);
    }//end getCcavenueConfigData()
}
