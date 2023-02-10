<?php
declare(strict_types=1);

namespace HookahShisha\CcavenueGraphQl\Model\Resolver;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use HookahShisha\CcavenueGraphQl\Logger\Logger;
use Magento\Framework\Api\SearchCriteriaBuilder;
use HookahShisha\CcavenueGraphQl\Helper\Data;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Infibeam\Ccavenue\Helper\Ccavenue;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class UpdateOrderStatus implements ResolverInterface
{

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
 
    /**
     * @var Logger
     */
    private Logger $ccavLogger;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    /**
     * @var Transaction
     */
    private Transaction $transaction;

    /**
     * @var OrderSender
     */
    private OrderSender $orderSender;

    /**
     * @var Ccavenue
     */
    private Ccavenue $checkoutHelper;

    /**
     * @var CurrencyInterface
     */
    private CurrencyInterface $localeCurrency;

    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cart;

    /**
     * UpdateOrderStatus constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface     $scopeConfig
     * @param StoreManagerInterface    $storeManager
     * @param Logger                   $ccavLogger
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param Data                     $helper
     * @param InvoiceService           $invoiceService
     * @param Transaction              $transaction
     * @param OrderSender              $orderSender
     * @param Ccavenue                 $checkoutHelper
     * @param CurrencyInterface        $localeCurrency
     * @param CartManagementInterface  $cartManagement
     * @param CartRepositoryInterface  $cart
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Logger $ccavLogger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helper,
        InvoiceService $invoiceService,
        Transaction $transaction,
        OrderSender $orderSender,
        Ccavenue $checkoutHelper,
        CurrencyInterface $localeCurrency,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cart
    ) {
        $this->orderRepository       = $orderRepository;
        $this->scopeConfig           = $scopeConfig;
        $this->storeManager          = $storeManager;
        $this->ccavLogger            = $ccavLogger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper                = $helper;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->orderSender = $orderSender;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_localecurrency = $localeCurrency;
        $this->cartManagement = $cartManagement;
        $this->cart = $cart;
    }//end __construct()

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $incrOrderId = $this->getOrderId($args);

        $orderId = $this->getOrderIdByIncrementId($incrOrderId);

        try {
            $ccData = $this->setOrderStaus($orderId, $incrOrderId);
            return $ccData;
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
    }//end resolve()

    /**
     * Check if order id has been passed or not.
     *
     * @param  array $args
     */
    private function getOrderId(array $args): string
    {
        if (!isset($args['orderId'])) {
            throw new GraphQlInputException(__('"order id should be specified'));
        }

        return $args['orderId'];
    }//end getOrderId()

    /**
     * Set order status using order id.
     *
     * @param  int $orderId
     * @param  string $orderIncId
     */
    public function setOrderStaus($orderId, $orderIncId)
    {
            $response = $this->helper->ccavenueStatusApiResponse($orderIncId);

            $logStatus = (bool)$this->helper->getCCaveCusData('graphql_ccavenue/general_Setting/enable_log');

        if ($logStatus) {
            $this->ccavLogger->info('api response data (in array)');
            $this->ccavLogger->info(var_export($response, true));
        }

            $redirectUrl = $this->helper->getCCaveCusData('graphql_ccavenue/general_Setting/redirect_url');
            $cancelUrl = $this->helper->getCCaveCusData('graphql_ccavenue/general_Setting/cancel_url');
            $failedUrl = $this->helper->getCCaveCusData('graphql_ccavenue/general_Setting/failed_url');

            $message = '';

            $returnData = [];
            
        if (isset($response) && array_key_exists("order_no", $response)) {

            $resIncId = $response['order_no'];
            $resOrderId = $this->getOrderIdByIncrementId($resIncId);

            $resAmount = round($response['order_amt'], 2);
            $resCurrency = $response['order_currncy'];
                
            /*
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         */
            $order = $this->orderRepository->get($resOrderId);

            $payment = $order->getPayment();

            $reqOrderId = $order->getIncrementId();
            $reqAmount = round($order->getGrandTotal(), 2);
            $reqCurrency = $order->getOrderCurrencyCode();

            if ($reqOrderId == $resIncId && $reqAmount == $resAmount && $reqCurrency == $resCurrency) {
                $this->postProcessing($order, $payment, $response);
                if ($response['order_status'] == 'Shipped') {
                    if ($this->getCcavenueConfigData('payment_auto_invoice')) {
                        $this->_createInvoice($order->getId());
                    }

                    $this->deActiveQuote($order);
                    $message = '';
                    $returnData['return_url'] = $redirectUrl;
                    $returnData['success_message'] =  $response['order_bank_response'];
                    $returnData['order_status'] = 'true';
                } else {
                    $message = $response['order_bank_response'] ?? 'Payment failed, please try again';
                    $returnData['return_url'] = $cancelUrl;
                    $returnData['success_message'] = $message;
                    $returnData['order_status'] = 'false';
                }
            } else {
                $order->setState('fraud')->setStatus('fraud');
                $order->addStatusHistoryComment('Payment has been failed.', false);
                $order->save();
                $payment->setIsTransactionClosed(0);
                $payment->place();
                $returnData['return_url'] = $cancelUrl;
                $returnData['success_message'] = 'Security error : Illegal access detected.';
                $returnData['order_status'] = 'false';
            }

        } else {
            $returnData['return_url'] = $cancelUrl;
            $returnData['success_message'] = 'Payment failed. Please try again or choose a different payment method.';
            $returnData['order_status'] = 'false';
        }

        if ($message!='') {
            $this->cancelCurrentOrder($order->getId(), $message);
            $this->ccavLogger->info($message);
        }

        return $returnData;
    }//end setOrderStaus()

    /**
     * Get config setting data related to ccavenue.
     *
     * @param  string $field
     */
    public function getCcavenueConfigData($field)
    {
        $path    = 'payment/ccavenue/'.$field;
        $storeid = $this->storeManager->getStore()->getStoreId();
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
    }//end getCcavenueConfigData()

    /**
     * Get order is by increment id.
     *
     * @param  int $incrementId
     */
    public function getOrderIdByIncrementId($incrementId)
    {
        $orderId = null;
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
            $orderData      = $this->orderRepository->getList($searchCriteria)->getItems();
            foreach ($orderData as $order) {
                $orderId = (int) $order->getId();
            }
        } catch (Exception $exception) {
            $this->ccavLogger->error($exception->getMessage());
        }

        return $orderId;
    }//end getOrderIdByIncrementId()

    /**
     * Update order related info
     *
     * @param  object $order
     * @param  object $payment
     * @param  array $response
     */
    public function postProcessing(\Magento\Sales\Model\Order $order, \Magento\Framework\DataObject $payment, $response)
    {
       
        $isCustomerNotified = false;

        if (isset($response['order_status']) && $response['order_status'] == 'Shipped') {
            $orderSts = "Scuccess";
        } else {
            $orderSts = "Failure";
        }

        if (isset($response['order_card_type']) && $response['order_card_type'] == 'CRDC') {
            $pmode = "Credit Card";
        } else {
            $pmode = "Debit Card";
        }

        $txtId = $response['reference_no'];
        $ordAmt = $response['order_amt'];

        $orderComment = 'Txn Id: '.$txtId.' | Txn Status: '.$orderSts.' | Payment Mode: '. $pmode.' | Amount: '.$ordAmt;

        $payment->setTransactionId($response['reference_no']);

        if ($response['order_status'] != 'Shipped') {
            $payment->setTransactionAdditionalInfo('status_message', $response['order_bank_response']);
            $payment->setTransactionAdditionalInfo('payment_mode', $pmode);
        } else {
            $isCustomerNotified = true;
            $payment->setTransactionAdditionalInfo('status_message', $response['order_bank_response']);
            $payment->setTransactionAdditionalInfo('payment_mode', $pmode);
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus($this->getCcavenueConfigData('payment_success_order_status'));
            $order->setCustomerNoteNotify(true);
        }

        $order->addStatusHistoryComment($orderComment)
                ->setIsCustomerNotified($isCustomerNotified);
        $order->save();

        $this->ccavLogger->info($response['order_no'] . ' | ' . $orderComment);

        $payment->setIsTransactionClosed(0);
        $payment->place();

        if ($response['order_status'] == 'Shipped') {
            $this->sendOrderMail($order);
            $this->ccavLogger->info('Order Confirmation Email Sent.');
        }
    }//end postProcessing()

    /**
     * Send order email
     *
     * @param  object $order
     */
    public function sendOrderMail($order)
    {
        $this->orderSender->send($order, false, true);
    }//end sendOrderMail()

    /**
     * Create auto order invoice
     *
     * @param  int $orderId
     */
    public function _createInvoice($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->canInvoice()) {
            $invoice = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->save();
            $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $order->addStatusHistoryComment('Automatically INVOICED.', false)->save();
            $this->ccavLogger->info($orderId.' Automatically INVOICED.');
        }
    }//end _createInvoice()

    /**
     * Cancel order
     *
     * @param  int $orderId
     * @param  string $comment
     */
    public function cancelCurrentOrder($orderId, $comment = '')
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
        }
    }//end cancelCurrentOrder()

    /**
     * Deactive quote
     *
     * @return void
     * @throws Exception
     * @param object $order
     */
    protected function deActiveQuote($order)
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->cart->get($quoteId);
        $quote->setReservedOrderId($order->getId());
        $quote->setIsActive(false);
        $this->cart->save($quote);
    }//end deActiveQuote()
}//end class
