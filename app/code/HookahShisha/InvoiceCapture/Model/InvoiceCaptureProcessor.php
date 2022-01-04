<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Exception;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

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
     * @var \Magento\Framework\DB\Transaction
     */
    private $transactionFactory;

    /**
     * @param Config $config
     * @param OrderProvider $orderProvider
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Config $config,
        OrderProvider $orderProvider,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory
    ) {
        $this->config = $config;
        $this->orderProvider = $orderProvider;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
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
                $this->logger->error($exception->getMessage());
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
     * @param Order $order
     */
    public function processInvoiceStep($order)
    {
        $invoice = $this->prepareInvoice($order);
        $invoice = $this->saveTransaction($invoice);
    }

    /**
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
            $this->logger->error($e->getMessage());
        }
        return $invoice;
    }

    /**
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
            $this->logger->error($e->getMessage());
        }
        return $invoice;
    }
}
