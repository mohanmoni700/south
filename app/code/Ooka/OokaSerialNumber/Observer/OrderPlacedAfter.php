<?php

declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderPlacedAfter implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var OrderItemRepositoryInterface
     */
    private OrderItemRepositoryInterface $orderItemRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute method to set product attribute in orderlevel
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */
        $order = $observer->getEvent()->getOrder();

        if (!empty($order) && !empty($order->getAllVisibleItems())) {
            $orderItems = $order->getAllVisibleItems();
            $updatedOrderItems = [];
            foreach ($orderItems as $item) {
                $product = $this->productRepository->get($item->getSku(), false, $order->getStoreId());
                $item->setData("is_serialize", $product->getData("ooka_require_serial_number"));
                $updatedOrderItems [] = $item;
            }
            $order->setItems($updatedOrderItems);
            $this->orderRepository->save($order);
        }
    }
}
