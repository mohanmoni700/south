<?php
declare(strict_types=1);

namespace Alfakher\ExitB\Model;

use Magento\Framework\Model\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;
use Alfakher\ExitB\Model\ExitbOrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * ExitB order sync
 */
class ExitbSync
{
    public const EXITB_ENABLE = 'exitb/general/enabled';
    public const AUTH_API = 'exitb/exitb_auth/auth_api';
    public const CLINT_CODE = 'exitb/exitb_auth/auth_clientcode';
    public const API_KEY = 'exitb/exitb_auth/auth_apikey';
    public const ORDER_API = 'exitb/exitb_ordersync/order_api';
    public const ORDER_PREFIX = 'exitb/exitb_ordersync/prefix_order';
    public const ORDER_ISB2B = 'exitb/exitb_ordersync/order_isb2b';
    public const ORDER_ADM = 'exitb/exitb_ordersync/ad_medium';
    public const SHIP_CODE = 'exitb/exitb_ordersync/ship_code';
    public const PAYMENT = 'exitb/exitb_ordersync/payment';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $order;

    /**
     * @var Curl $curl
     */
    protected $curl;

    /**
     * @var Json $json
     */
    protected $json;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ExitbOrderFactory
     */
    protected $exitbmodelFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * New construct
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param Curl $curl
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param ExitbOrderFactory $exitbmodelFactory
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        Context                    $context,
        ScopeConfigInterface       $scopeConfig,
        OrderRepositoryInterface   $orderRepository,
        Curl                       $curl,
        Json                       $json,
        ManagerInterface           $messageManager,
        ExitbOrderFactory          $exitbmodelFactory,
        CompanyRepositoryInterface $companyRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->order = $orderRepository;
        $this->curl = $curl;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->exitbmodelFactory = $exitbmodelFactory;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Get website Config Value
     *
     * @param mixed $config_path
     * @param int $WebsiteId
     * @return string
     */
    public function getConfigValue($config_path, $WebsiteId = null)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_WEBSITE,
            $WebsiteId
        );
    }

    /**
     * Module enable
     *
     * @param int $websiteId
     * @return boolean
     */
    public function isModuleEnabled($websiteId)
    {
        return (bool)$this->getConfigValue(self::EXITB_ENABLE, $websiteId);
    }

    /**
     * Order sync
     *
     * @param int $orderId
     * @param mixed $token
     * @return mixed
     */
    public function orderSync($orderId, $token)
    {
        try {
            if (isset($orderId) && !empty($orderId) && !empty($token)) {
                $order = $this->order->get($orderId);

                $orderData = [];
                $websiteId = $order->getStore()->getWebsiteId();
                $orderData['orderData']['number'] = $this->getConfigValue(
                        self::ORDER_PREFIX,
                        $websiteId
                    ) . '-' . $order->getIncrementId();
                $orderData['orderData']['externalNumber'] = $order->getEntityId();
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
                } else {
                    $orderData['orderData']['customer']['number'] = $order->getCustomerEmail();
                }
                $orderData['orderData']['customer']['email'] = $order->getCustomerEmail();
                $orderData['orderData']['customer']['phone'] = $order->getShippingAddress()->getTelephone();

                $companydata = $order->getExtensionAttributes()->getCompanyOrderAttributes();
                $companyName = '';
                if (!empty($companydata)) {
                    $company = $this->companyRepository->get($companydata->getCompanyId());
                    $orderData['orderData']['customer']['taxIdNumber'] = $company->getData('fiscal_number');
                    $companyName = $companydata->getCompanyName();
                }

                $shippingaddress = $order->getShippingAddress();
                $orderData['orderData']['deliveryAddress'] = $this->deliveryAddress($shippingaddress, $companyName);
                $billingaddress = $order->getBillingAddress();
                $orderData['orderData']['invoiceAddress'] = $this->invoiceAddress($billingaddress, $companyName);

                $paymentCode = $order->getPayment()->getMethod();
                $orderData['orderData']['payment']['code'] = $this->paymentType($websiteId, $paymentCode);
                if ($orderData['orderData']['isB2B']) {
                    $orderData['orderData']['payment']['token'] = $order->getMonduReferenceId();
                } else {
                    $klarnaToken = $order->getPayment()->getAdditionalInformation('klarna_order_id');
                    $vrToken = $order->getPayment()->getAdditionalInformation('REFERENCE_ID');
                    $orderData['orderData']['payment']['token'] = $klarnaToken ? $klarnaToken : $vrToken;
                }
                if ($order->hasInvoices()) {
                    $orderData['orderData']['payment']['isPayed'] = true;
                    $orderData['orderData']['payment']['amountPayed'] = (float)$order->getPayment()->getAmountPaid();
                } else {
                    if (str_contains($paymentCode, 'vrpayecommerce') || str_contains($paymentCode, 'klarna')) {
                        $orderData['orderData']['payment']['isPayed'] = false;
                    }
                }

                $shippingMethod = $order->getShippingMethod();
                $orderData['orderData']['shipment']['code'] = $this->getConfigValue(self::SHIP_CODE, $websiteId);
                $orderData['orderData']['shipment']['total'] = (float)$order->getShippingAmount() + (float)$order->getShippingTaxAmount() + (float)$order->getHandlingFee();
                if ($order->getCouponCode() != null && $order->getDiscountAmount() != 0) {
                    $orderData['orderData']['vouchers'][] = [
                        "code" => $order->getCouponCode(),
                        "discount" => (float)abs($order->getDiscountAmount())
                    ];
                }
                $items = $order->getAllItems();
                $orderData['orderData']['items'] = $this->orderItems($items, $orderData['orderData']['isB2B']);
                $exitBModel = $this->exitbmodelFactory->create();
                $exitBorderSync = $exitBModel->load($orderId, 'order_id');

                if ($exitBorderSync->getSyncStatus() == 1) {
                    return $exitBorderSync;
                } elseif ($exitBorderSync->getSyncStatus() == 2 || $exitBorderSync->getSyncStatus() == 3) {
                    $updateData = $this->orderExist($exitBorderSync, $token, $websiteId, $orderData);
                    return $updateData;
                } else {
                    $creatModel = $this->exitbmodelFactory->create();
                    $creatModel->setData('order_id', $order->getEntityId());
                    $creatModel->setData('customer_email', $order->getCustomerEmail());
                    $creatModel->setData('increment_id', $order->getIncrementId());
                    $creatModel->setData('sync_status', 2);
                    $creatModel->save();
                    if (!empty($creatModel->getEntityId())) {
                        $newData = $this->orderExist($creatModel, $token, $websiteId, $orderData);
                        return $newData;
                    }
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Order record update
     *
     * @param mixed $data
     * @param mixed $token
     * @param int $websiteId
     * @param mixed $orderData
     * @return ExitbOrderFactory
     */
    public function orderExist($data, $token, $websiteId, $orderData)
    {
        $result = $this->orderSyncApi($token, $websiteId, $orderData);
        $result_message = $this->json->unserialize($result)['success'];
        $status = $result_message === true ? 1 : 3;
        $data->setData('request_param', $this->json->serialize($orderData));
        $data->setData('response_param', $result);
        $data->setData('sync_status', $status);
        $result = $data->save();
        return $result;
    }

    /**
     * Order sync api
     *
     * @param mixed $token
     * @param int $websiteId
     * @param mixed $orderData
     * @return mixed
     */
    public function orderSyncApi($token, $websiteId, $orderData)
    {
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Authorization', 'Bearer ' . $token);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->post(
            $this->getConfigValue(
                self::ORDER_API,
                $websiteId
            ),
            $this->json->serialize($orderData)
        );
        return $this->curl->getBody();
    }

    /**
     * Get token Config Value
     *
     * @param int $websiteId
     * @return mixed
     */
    public function tokenAuthentication($websiteId)
    {
        if ($this->isModuleEnabled($websiteId)) {
            $authData = [
                'client' => $this->getConfigValue(self::CLINT_CODE, $websiteId),
                'apiKey' => $this->getConfigValue(self::API_KEY, $websiteId),
            ];
            if (!empty($authData['client']) && !empty($authData['apiKey'])) {
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->post($this->getConfigValue(self::AUTH_API, $websiteId), $authData);
                $response = $this->curl->getBody();
                $token = $this->json->unserialize($response, true)['response']['jwt'];
                return $token;
            }
        } else {
            return '';
        }
    }

    /**
     * Get delivery address
     *
     * @param array $shippingaddress
     * @param string|null $companyName
     * @return array
     */
    public function deliveryAddress($shippingaddress, $companyName = null)
    {
        if ($shippingaddress) {
            return [
                'firstName' => $shippingaddress->getFirstname(),
                'lastName' => $shippingaddress->getLastname(),
                'company' => $companyName,
                'street' => $shippingaddress->getStreetLine(1),
                'houseNumber' => $shippingaddress->getStreetLine(2),
                'zip' => $shippingaddress->getPostcode(),
                'city' => $shippingaddress->getCity(),
                'countryCode' => $shippingaddress->getCountryId(),
            ];
        }
    }

    /**
     * Get invoice address
     *
     * @param array $billingaddress
     * @param string|null $companyName
     * @return array
     */
    public function invoiceAddress($billingaddress, $companyName = null)
    {
        if ($billingaddress) {
            return [
                'firstName' => $billingaddress->getFirstname(),
                'lastName' => $billingaddress->getLastname(),
                'company' => $companyName,
                'street' => $billingaddress->getStreetLine(1),
                'houseNumber' => $billingaddress->getStreetLine(2),
                'zip' => $billingaddress->getPostcode(),
                'city' => $billingaddress->getCity(),
                'countryCode' => $billingaddress->getCountryId(),
            ];
        }
    }

    /**
     * Get payment
     *
     * @param int $websiteId
     * @param string $paymentCode
     * @return mixed
     */
    public function paymentType($websiteId, $paymentCode)
    {
        $exitBCode = '';
        $paymentconfig = $this->getConfigValue(self::PAYMENT, $websiteId);
        if ($paymentconfig) {
            $unserializeData = $this->json->unserialize($paymentconfig, true);
            foreach ($unserializeData as $key => $row) {
                if ($row['payment_method'] == $paymentCode) {
                    $exitBCode = $row['method_code'];
                }
            }
            return (!empty($exitBCode)) ? $exitBCode : '';
        }
        return '';
    }

    /**
     * Get order items
     *
     * @param mixed $items
     * @param string $isB2B
     * @return array
     */
    public function orderItems($items, $isB2B)
    {
        $productData = array();
        foreach ($items as $key => $item) {
            $productData[$key]['externalNumber'] = $item->getItemId();
            $vhsArticleNumber = $item->getProduct()->getData('vhsarticlenumber');
            $ean = $item->getProduct()->getData('ean');
            $articleNumber = $item->getProduct()->getData('articlenumber');
            if (!empty($vhsArticleNumber)) {
                $productData[$key]['vhsArticleNumber'] = $vhsArticleNumber;
            } elseif (!empty($ean)) {
                $productData[$key]['ean13'] = $ean;
            } elseif (!empty($articleNumber)) {
                $productData[$key]['articleNumber'] = $articleNumber;
            }
            $productData[$key]['quantity'] = (int)$item->getQtyOrdered();
            $itemPriceAndDiscount = $this->getItemPriceAndDiscount($item, $isB2B);
            $productData[$key] = array_merge($productData[$key], $itemPriceAndDiscount);
        }
        return $productData;
    }

    /**
     * Get Item Price And Discount based on B2B, B2C
     * @param $item
     * @param $isB2B
     * @return array
     */
    private function getItemPriceAndDiscount($item, $isB2B)
    {
        $itemQty = (int)$item->getQtyOrdered();
        $discountAmount = (float)$item->getDiscountAmount();
        $itemPrice = (float)$item->getPrice();

        $netDiscount = ($discountAmount ? $discountAmount / $itemQty : 0);
        $netDiscount = number_format((float)$netDiscount, 2, '.', '');
        $netPrice = ($itemPrice - $netDiscount);
        $netPrice = number_format($netPrice, 2, '.', '');

        if ($isB2B) {
            return [
                'priceNet' => (float)$netPrice,
                'discountNet' => (float)$netDiscount,
                'price' => 0,
                'discount' => 0
            ];
        }
        return [
            'priceNet' => 0,
            'discountNet' => 0,
            'price' => (float)$netPrice,
            'discount' => (float)$netDiscount
        ];
    }
}
