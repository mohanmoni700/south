<?php

namespace Alfakher\ExitB\Controller\Index;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class order sync
 */
class Index extends \Magento\Framework\App\Action\Action
{
    public const PREFIX_ORDER = 'exitb/exitb_ordersync/prefix_order';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Alfakher\ExitB\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * New con
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Alfakher\ExitB\Helper\Data $helperData
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Alfakher\ExitB\Helper\Data $helperData,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->pageFactory = $pageFactory;
        $this->order = $orderRepository;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId ="7484";
        
        $id = $this->getRequest()->getParam('id');
        try {
            if (isset($id) && !empty($id)) {
                $order = $this->order->get($orderId);

                $orderData = [];

                $websiteId = $order->getStore()->getWebsiteId();

                $orderData['orderData']['number'] = $this->helperData->getConfigValue(self::PREFIX_ORDER, $websiteId)
                .'-'.$order->getIncrementId();
                // $orderData['externalNumber'] = $order->getIncrementId();
                $orderData['orderData']['date'] = $order->getCreatedAt();
                $orderData['orderData']['currency'] = $order->getOrderCurrencyCode();
                $orderData['orderData']['isB2B'] = filter_var($this->helperData->getConfigValue('exitb/exitb_ordersync/order_isb2b', $websiteId), FILTER_VALIDATE_BOOLEAN);

                $orderData['orderData']['advertisingMedium']['code'] = $this->helperData->getConfigValue('exitb/exitb_ordersync/ad_medium', $websiteId);

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
                $orderData['orderData']['shipment']['code'] = $this->helperData->getConfigValue('exitb/exitb_ordersync/ship_code', $websiteId);

                $items = $order->getAllItems();
                $orderData['orderData']['items'] = $this->orderItems($items);
                
                $token = $this->tokenAuthentication($websiteId);

                $this->curl->addHeader('Content-Type', 'application/json');
                $this->curl->addHeader('Authorization', 'Bearer '.$token);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                // $this->curl->post($this->helperData->getConfigValue('exitb/exitb_ordersync/order_api', $websiteId), $this->json->serialize($orderData));

            // output of curl requestt
                $result = $this->curl->getBody();
                echo '<pre>';
                print_r($orderData);
                echo '</pre>';
                exit;
                echo '<pre>';
                print_r($this->json->serialize($orderData));
                echo '</pre>';
                exit;

            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function tokenAuthentication($websiteId)
    {
        $authData = [
            'client' => $this->helperData->getConfigValue('exitb/exitb_auth/auth_clientcode', $websiteId),
            'apiKey' => $this->helperData->getConfigValue('exitb/exitb_auth/auth_apikey', $websiteId)
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post($this->helperData->getConfigValue('exitb/exitb_auth/auth_api', $websiteId), $authData);
        $response = $this->curl->getBody();
        $token = $this->json->unserialize($response, true)['response']['jwt'];
        return $token;
    }

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

    public function paymentType($websiteId, $isOffline)
    {
        $offline = $this->helperData->getConfigValue('exitb/exitb_ordersync/payment_offline', $websiteId);
        $online = $this->helperData->getConfigValue('exitb/exitb_ordersync/payment_online', $websiteId);
        return $isOffline ? $offline : $online;
    }

    public function orderItems($items)
    {
        foreach ($items as $key => $item) {
            $productData[$key]['sku']  = $item->getSku();
            $productData[$key]['name'] = $item->getName();
            // $vhsArticleNumber = $item->getvhsArticleNumber();
            // $articleNumber = $item->getarticleNumber();
            // $ean13 = $item->getean13();
            $productData[$key]['quantity'] = $item->getQtyOrdered();
            $price = $item->getPrice() * $item->getQtyOrdered();
            $taxAmount = $item->getBaseTaxAmount();
            $productData[$key]['price'] = $price + $taxAmount;
            $productData[$key]['discount'] = $item->getDiscountAmount();
               // print_r($item->getProductOptions());
        }
        return $productData;
        // $productData[] =array(
        //     'ean13' => "4260500000368",
        //     'quantity'=> 10,
        //     'price' => 21.30,
        //     'discount'=> 0
        // );
        // return $productData;
    }
}
