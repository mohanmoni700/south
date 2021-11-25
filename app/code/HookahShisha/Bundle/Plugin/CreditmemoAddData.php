<?php
namespace HookahShisha\Bundle\Plugin;

use Magento\Sales\Api\OrderItemRepositoryInterface;

class CreditmemoAddData
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

    public function beforeSave(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        \Magento\Sales\Api\Data\CreditmemoInterface $entity
    ) {
        /** @var CreditmemoInterface $creditmemo */
        foreach ($entity->getItems() as $item) {
            $orderItemId = $item->getOrderItemId();
            $orderItem = $this->orderItemRepository->get($orderItemId);
            $item->setAlfaIsBundle($orderItem->getAlfaIsBundle());
        }
    }
}
