<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Exception;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

/**
 * Order Invoice Capture Processor Model
 */
class InvoiceCaptureProcessor
{
    public const ONLINE_PAYMENT_METHOD = [
        "spreedly",
        "tabby_installments"
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderProvider
     */
    private $orderProvider;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var InvoiceCaptureLogger
     */
    private $invoiceCaptureLogger;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transactionFactory;
    /**
     * @var currentPaymentMethod
     */
    public $currentPaymentMethod;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     *  InvoiceCaptureProcessor constructor.
     *
     * @param Config $config
     * @param OrderProvider $orderProvider
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceCaptureLogger $invoiceCaptureLogger
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        Config $config,
        OrderProvider $orderProvider,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceCaptureLogger $invoiceCaptureLogger,
        InvoiceSender $invoiceSender
    ) {
        $this->config = $config;
        $this->orderProvider = $orderProvider;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceCaptureLogger = $invoiceCaptureLogger;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Invoice Capture Processor
     */
    public function execute()
    {
        $orders = $this->orderProvider->getEligibleOrders();
        foreach ($orders as $order) {
            try {
                //Invoice the order logic
                $this->processOrder($order);
            } catch (Exception $exception) {
                $message = "Error occured on Invoice Generate,  Please check log for more details";
                $trace = $exception->getMessage();
                $trace .= "\n". $exception->getTraceAsString();
                $this->invoiceCaptureLogger->logExceptionMessage($message, $trace);
            }
        }
    }

    /**
     * Change order status
     *
     * @param Order $order
     */
    public function processOrder(Order $order)
    {
        if ($order) {
            if (!$order->getShipmentsCollection()->count()) {
                return ;
            }
            $payment = $order->getPayment();
            if (is_object($payment) && $payment->getMethod()) {
                $this->currentPaymentMethod = $payment->getMethod();
            }
            if ($order->canInvoice()) {
                $this->processInvoiceStep($order);
            }
        }
    }

    /**
     * Process InvoiceStep
     *
     * @param Order $order
     */
    public function processInvoiceStep($order)
    {
        $invoice = $this->prepareInvoice($order);
        $this->saveTransaction($invoice);

        // send invoice emails
        try {
            $this->invoiceSender->send($invoice);
        } catch (\Exception $e) {
            $message = "We can\'t send the invoice email right now.";
            $trace = $e->getMessage();
            $trace .= "\n". $e->getTraceAsString();
            $this->invoiceCaptureLogger->logExceptionMessage($message, $trace);
        }
    }

    /**
     * Prepare Invoice
     *
     * @param Order $order
     * @return bool|Order\Invoice
     */
    public function prepareInvoice($order)
    {
        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            if (in_array($this->currentPaymentMethod, self::ONLINE_PAYMENT_METHOD)) {
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
            }
            $invoice->register();
        } catch (\Exception $e) {
            $invoice = false;
            $message = "Error occured on PrepareInvoice,  Please check log for more details";
            $trace = $e->getMessage();
            $trace .= "\n". $e->getTraceAsString();
            $this->invoiceCaptureLogger->logExceptionMessage($message, $trace);
        }
        return $invoice;
    }

    /**
     * Save transaction
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function saveTransaction($invoice)
    {
        try {
            if (!$invoice) {
                return $invoice;
            }
            $order = $invoice->getOrder();
            $transactionSave = $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($order);
            $transactionSave->save();
        } catch (\Exception $e) {
            $invoice = false;
            $message = "Error occured on Save Transaction,  Please check log for more details";
            $trace = $e->getMessage();
            $trace .= "\n". $e->getTraceAsString();
            $this->invoiceCaptureLogger->logExceptionMessage($message, $trace);
        }
        return $invoice;
    }
}
