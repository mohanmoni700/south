<?php

namespace HookahShisha\Bundle\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class BeforeShipment implements ObserverInterface
{
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * Item constructor.
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        foreach ($shipment->getItemsCollection() as $item) {
            $orderItemId = $item->getOrderItemId();
            $orderItem = $this->orderItemRepository->get($orderItemId);
            $item->setAlfaIsBundle($orderItem->getAlfaIsBundle());
        }
        return $this;
    }
}
