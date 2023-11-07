<?php

namespace Alfakher\OrderComment\Plugin\Order;

use Exception;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\AddComment;
use Psr\Log\LoggerInterface;

class SetStatusParam
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Set previous order status if the current status is empty string (ref: B2BHW-1489)
     *
     * @param AddComment $subject
     * @return array
     */
    public function beforeExecute(AddComment $subject): array
    {
        $data = $subject->getRequest()->getPost('history');
        $orderId = $subject->getRequest()->getParam('order_id');
        if ($orderId && isset($data['status']) && $data['status'] == '') {
            try {
                $order = $this->orderRepository->get($orderId);
                $data['status'] = $order->getStatus();
                $subject->getRequest()->setPostValue('history', $data);
            } catch (Exception $exception) {
                $this->logger->info('Error saving previous order status: ' . $exception->getMessage());
            }
        }
        return [];
    }
}
