<?php

namespace Alfakher\ExitB\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * ExitB helper
 *
 */
class Data extends AbstractHelper
{

    public const ORDER_PREFIX = 'exitb/exitb_ordersync/prefix_order';
    public const ORDER_ISB2B  = 'exitb/exitb_ordersync/order_isb2b';
    public const ORDER_ADM    = 'exitb/exitb_ordersync/ad_medium';
    public const SHIP_CODE = 'exitb/exitb_ordersync/ship_code';
    public const ORDER_API ='exitb/exitb_ordersync/order_api';
    public const CLINT_CODE ='exitb/exitb_auth/auth_clientcode';
    public const API_KEY ='exitb/exitb_auth/auth_apikey';
    public const AUTH_API = 'exitb/exitb_auth/auth_api';
    public const OFFLINE = 'exitb/exitb_ordersync/payment_offline';
    public const ONLINE = 'exitb/exitb_ordersync/payment_online';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl $curl
     */
    protected $curl;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json $json
     */
    protected $json;

    /**
     * New construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->order = $orderRepository;
        $this->curl = $curl;
        $this->json = $json;
    }

    /**
     * Get website Config Value
     *
     * @param mixed $config_path
     * @param int $WebsiteId
     */
    public function getConfigValue($config_path, $WebsiteId = null)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $WebsiteId
        );
    }

    /**
     * Order sync
     *
     * @param int $orderId
     * @param mixed $token
     */

    public function orderSync($orderId, $token)
    {

        try {
            if (isset($orderId) && !empty($orderId)) {
                $order = $this->order->get($orderId);

                $orderData = [];

                $websiteId = $order->getStore()->getWebsiteId();

                $orderData['orderData']['number'] = $this->getConfigValue(self::ORDER_PREFIX, $websiteId)
                .'-'.$order->getIncrementId();
                // $orderData['externalNumber'] = $order->getIncrementId();
                $orderData['orderData']['date'] = $order->getCreatedAt();
                $orderData['orderData']['currency'] = $order->getOrderCurrencyCode();
                $orderData['orderData']['isB2B'] = filter_var(
                    $this->getConfigValue(self::ORDER_ISB2B, $websiteId),
                    FILTER_VALIDATE_BOOLEAN
                );

                $orderData['orderData']['advertisingMedium']['code'] = $this->getConfigValue(
                    self::ORDER_ADM,
                    $websiteId
                );

                $customerId = $order->getCustomerId();
                if ($customerId) {
                    $orderData['orderData']['customer']['number'] = $order->getCustomerId();
                    $orderData['orderData']['customer']['email'] = $order->getCustomerEmail();
                }

                $shippingaddress = $order->getShippingAddress();
                $orderData['orderData']['deliveryAddress'] = $this->deliveryAddress($shippingaddress);

                $billingaddress = $order->getBillingAddress();
                $orderData['orderData']['invoiceAddress'] = $this->invoiceAddress($billingaddress);

                $isOffline = $order->getPayment()->getMethodInstance()->isOffline();
                $orderData['orderData']['payment']['code'] = $this->paymentType($websiteId, $isOffline);

                $shippingMethod  = $order->getShippingMethod();
                $orderData['orderData']['shipment']['code'] = $this->getConfigValue(self::SHIP_CODE, $websiteId);

                $items = $order->getAllItems();
                $orderData['orderData']['items'] = $this->orderItems($items);
                
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ordersync.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info(print_r($orderData, true));
                $logger->info(print_r($token, true));

                $this->curl->addHeader('Content-Type', 'application/json');
                $this->curl->addHeader('Authorization', 'Bearer '.$token);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->post($this->getConfigValue(
                    self::ORDER_API,
                    $websiteId
                ), $this->json->serialize($orderData));
                $result = $this->curl->getBody();
                $logger->info(print_r($this->json->unserialize($result, true)['message']));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
    /**
     * Get token Config Value
     *
     * @param int $websiteId
     */
    public function tokenAuthentication($websiteId)
    {
        $authData = [
            'client' => $this->getConfigValue(self::CLINT_CODE, $websiteId),
            'apiKey' => $this->getConfigValue(self::API_KEY, $websiteId)
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post($this->getConfigValue(self::AUTH_API, $websiteId), $authData);
        $response = $this->curl->getBody();
        $token = $this->json->unserialize($response, true)['response']['jwt'];
        return $token;
    }
    /**
     * Get delivery address
     *
     * @param array $shippingaddress
     */
    public function deliveryAddress($shippingaddress)
    {
        if ($shippingaddress) {
            return [
                'firstName'=> $shippingaddress->getFirstname(),
                'lastName' => $shippingaddress->getLastname(),
                'street'   => implode($shippingaddress->getStreet()),
                'zip'      => $shippingaddress->getPostcode(),
                'city'     => $shippingaddress->getCity(),
                'countryCode' => $shippingaddress->getCountryId()
            ];
        }
    }
    /**
     * Get invoice address
     *
     * @param array $billingaddress
     */
    public function invoiceAddress($billingaddress)
    {
        if ($billingaddress) {
            return [
                'firstName'=> $billingaddress->getFirstname(),
                'lastName' => $billingaddress->getLastname(),
                'street'   => implode($billingaddress->getStreet()),
                'zip'      => $billingaddress->getPostcode(),
                'city'     => $billingaddress->getCity(),
                'countryCode' => $billingaddress->getCountryId()
            ];
        }
    }
    /**
     * Get payment
     *
     * @param int $websiteId
     * @param mixed $isOffline
     */
    public function paymentType($websiteId, $isOffline)
    {
        $offline = $this->getConfigValue(self::OFFLINE, $websiteId);
        $online = $this->getConfigValue(self::ONLINE, $websiteId);
        return $isOffline ? $offline : $online;
    }
    /**
     * Get order items
     *
     * @param mixed $items
     */
    public function orderItems($items)
    {
        // foreach ($items as $key => $item) {
        //     $productData[$key]['sku']  = $item->getSku();
        //     $productData[$key]['name'] = $item->getName();
        //     // $vhsArticleNumber = $item->getvhsArticleNumber();
        //     // $articleNumber = $item->getarticleNumber();
        //     // $ean13 = $item->getean13();
        //     $productData[$key]['quantity'] = $item->getQtyOrdered();
        //     $price = $item->getPrice() * $item->getQtyOrdered();
        //     $taxAmount = $item->getBaseTaxAmount();
        //     $productData[$key]['price'] = $price + $taxAmount;
        //     $productData[$key]['discount'] = $item->getDiscountAmount();
        //        // print_r($item->getProductOptions());
        // }
        // return $productData;
        $productData[] =[
            'ean13' => "4260500000368",
            'quantity'=> 1,
            'price' => 21.30,
            'discount'=> 0
        ];
        return $productData;
    }
}
