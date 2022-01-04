<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Exception;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order;

/**
 * Order Invoice Capture Processor Model
 */
class InvoiceCaptureProcessor
{
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
     *  InvoiceCaptureProcessor constructor.
     *
     * @param Config $config
     * @param OrderProvider $orderProvider
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceCaptureLogger $invoiceCaptureLogger
     */
    public function __construct(
        Config $config,
        OrderProvider $orderProvider,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceCaptureLogger $invoiceCaptureLogger
    ) {
        $this->config = $config;
        $this->orderProvider = $orderProvider;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceCaptureLogger = $invoiceCaptureLogger;
    }

    /**
     * Invoice Capture Processor
     *
     * @param array $orderIds
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
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processOrder(Order $order)
    {
        if ($order) {
            if (!$order->getShipmentsCollection()->count()) {
                return ;
            }
            if ($order->canInvoice()) {
                $this->processInvoiceStep($order);
            }
        }
        return $this;
    }

    /**
     * Process InvoiceStep
     *
     * @param Order $order
     */
    public function processInvoiceStep($order)
    {
        $invoice = $this->prepareInvoice($order);
        $invoice = $this->saveTransaction($invoice);
    }

    /**
     * Prepare Invoice
     *
     * @param Order $order
     * @return Invoice
     */
    public function prepareInvoice($order)
    {
        try {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
            $invoice->register();
        } catch (\Exception $e) {
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
            $message = "Error occured on Save Transaction,  Please check log for more details";
            $trace = $e->getMessage();
            $trace .= "\n". $e->getTraceAsString();
            $this->invoiceCaptureLogger->logExceptionMessage($message, $trace);
        }
        return $invoice;
    }
}
