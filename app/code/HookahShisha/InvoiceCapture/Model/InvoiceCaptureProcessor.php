<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Exception;
use Magento\Sales\Api\OrderRepositoryInterface;
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
     * @param Config $config
     * @param OrderProvider $orderProvider
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Config $config,
        OrderProvider $orderProvider,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->orderProvider = $orderProvider;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
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
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }
}
