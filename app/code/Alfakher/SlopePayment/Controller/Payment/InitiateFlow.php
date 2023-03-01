<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Controller\Payment;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Logger\Logger;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;

class InitiateFlow extends Action
{
    public const CREATE_ORDER = '/orders';
    public const FIND_ORDER = '/orders/';
    public const UPDATE_ORDER = '/orders/id/';
    public const RESET_ORDER = '/orders/id/reset';
    public const GET_ORDER_INTENT = '/orders/id/intent?timeoutMs=36000000';
    public const INVALID_ORDER_TOTAL = 'invalid-order-total';

    /**
     * JsonFactory
     *
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteItemRepository
     */
    protected $quoteItemRepository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @var GatewayRequest
     */
    protected $gatewayRequest;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CheckoutSession $checkoutSession
     * @param QuoteItemRepository $quoteItemRepository
     * @param Json $json
     * @param SlopeConfigHelper $slopeConfig
     * @param GatewayRequest $gatewayRequest
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CheckoutSession $checkoutSession,
        QuoteItemRepository $quoteItemRepository,
        Json $json,
        SlopeConfigHelper $slopeConfig,
        GatewayRequest $gatewayRequest,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->json = $json;
        $this->config = $slopeConfig;
        $this->gatewayRequest = $gatewayRequest;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Initiate checkout flow
     *
     * @return JsonFactory
     * @throws Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $messages = ['Some error occured, Please try again later'];
            $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
            $mgtOrder = $this->getMgtOrderForSlope();
            $mgtQuoteId = $this->checkoutSession->getQuote()->getId();
            $slopeOrder = $this->findSlopeOrder($mgtQuoteId);

            $statusCode = isset($slopeOrder['statusCode']) ? $slopeOrder['statusCode'] : null;
            if (isset($slopeOrder) && $statusCode === 404) {
                $slopeOrder = $this->createNewSlopeOrder($mgtOrder);
                if (isset($slopeOrder['code']) && $slopeOrder['code'] !== '') {
                    $messages = $slopeOrder['messages'];
                    return $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
                }
            }

            if (isset($slopeOrder) && isset($slopeOrder['id']) && $slopeOrder['id'] != '') {
                $slopeOrderId = $slopeOrder['id'];
                $slopePopup = $this->getSlopeOrderIntent($slopeOrderId);
            }

            if (isset($slopePopup['secret']) && $slopePopup['secret'] != '') {
                $result->setData(['success' => true, 'secret' => $slopePopup['secret'], 'messages' => '']);
            } else {
                $messages = ['Some error occured, Please try again later'];
                $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
            }
        } catch (\Exception $e) {
            if ($this->config->isDebugEnabled()) {
                $this->logger->info('Slope Checkout Error:' . $e->getMessage());
            }
            return $result->setData(['success' => false, 'secret' => null, 'messages' => $messages]);
        }
        return $result;
    }

    /**
     * Retrieve quote item data
     *
     * @return array
     */
    private function getQuoteItemsforSlope()
    {
        $quoteItemData = [];
        $items = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->quoteItemRepository->getList($quoteId);
            foreach ($quoteItems as $quoteItem) {
                $product = $quoteItem->getProduct();
                $quoteItemData['externalId'] = $product->getId();
                $quoteItemData['sku'] = $product->getSku();
                $quoteItemData['orderId'] = $quoteId;
                $quoteItemData['name'] = $product->getName();
                $quoteItemData['description'] = $product->getDescription();
                $quoteItemData['quantity'] = $quoteItem->getTotalQty();
                $quoteItemData['unitPrice'] = $product->getPrice() * 100;
                $quoteItemData['price'] = $quoteItem->getCalculationPrice() * 100;
                $quoteItemData['url'] = $product->getProductUrl();
                $quoteItemData['createdAt'] = $product->getCreatedAt();
                $quoteItemData['updatedAt'] = $product->getUpdatedAt();
                $items[] = $quoteItemData;
            }
        }
        return $items;
    }

    /**
     * Create new slope order
     *
     * @param array $order
     * @return array
     */
    public function createNewSlopeOrder($order)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();

        $url = $apiEndpointUrl . self::CREATE_ORDER;
        $response = $this->gatewayRequest->post($url, $order);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Update slope order
     *
     * @param int $slopeOrderId
     * @return array
     */
    public function updateSlopeOrder($slopeOrderId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $order = $this->getMgtOrderForSlope();
        $url = $apiEndpointUrl . self::UPDATE_ORDER;
        $url = str_replace("id", $slopeOrderId, $url);
        $response = $this->gatewayRequest->post($url, $order);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Reset slope order
     *
     * @param int $slopeOrderId
     * @return array
     */
    public function resetSlopeOrder($slopeOrderId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $order = $this->getMgtOrderForSlope();
        $url = $apiEndpointUrl . self::RESET_ORDER;
        $url = str_replace("id", $slopeOrderId, $url);
        $response = $this->gatewayRequest->post($url, $order);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Find slope order by externalId
     *
     * @param int $externalId
     * @return array
     */
    public function findSlopeOrder($externalId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $url = $apiEndpointUrl . self::FIND_ORDER . $externalId;
        $response = $this->gatewayRequest->post($url);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Get slope order intent secret
     *
     * @param int $slopeOrderId
     * @return array
     */
    public function getSlopeOrderIntent($slopeOrderId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();

        /* NOTE : Update order data with latest quote before opening popup every time to keep data uptodate*/
        $updateOrder = $this->updateSlopeOrder($slopeOrderId);

        if (isset($updateOrder['code']) && $updateOrder['code'] === self::INVALID_ORDER_TOTAL) {
            $this->resetSlopeOrder($slopeOrderId);
            $this->updateSlopeOrder($slopeOrderId);
        }

        $url = $apiEndpointUrl . self::GET_ORDER_INTENT;
        $url = str_replace("id", $slopeOrderId, $url);
        $response = $this->gatewayRequest->post($url);
        $response = $this->json->unserialize($response);
        return $response;
    }

    /**
     * Prepare order data for slope api
     *
     * @return array
     */
    public function getMgtOrderForSlope()
    {
        $orderData = [];

        $quote = $this->checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $billPhone = $billingAddress->getTelephone();

        $company = $this->config->getCustomerCompany($quote->getCustomerId());

        $address =
            [
            "line1" => $billingAddress->getStreet()[0],
            "city" => $billingAddress->getCity(),
            "state" => $billingAddress->getRegionCode(),
            "postalCode" => $billingAddress->getPostcode(),
            "country" => $billingAddress->getCountry(),
        ];
        $grandTotal = number_format((float)$quote->getGrandTotal(), 2, '.', '');

        $orderData['total'] = $grandTotal * 100;
        $orderData['currency'] = strtolower($quote->getQuoteCurrencyCode());
        $orderData['billingAddress'] = $address;
        $orderData['externalId'] = $quote->getId();
        $orderData['customer']['email'] = $quote->getCustomerEmail();
        $orderData['customer']['phone'] = $this->config->getSlopeFormattedPhone($billPhone);
        $orderData['customer']['businessName'] = $company->getCompanyName() ?: 'NA';
        $orderData['customer']['address'] = $address;
        $orderData['customer']['externalId'] = $quote->getCustomerId();

        return $this->json->serialize($orderData);
    }
}
