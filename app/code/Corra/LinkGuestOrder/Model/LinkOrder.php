<?php

namespace Corra\LinkGuestOrder\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\ResourceModel\Order as ResourceModel;
use Magento\Sales\Model\OrderFactory;

class LinkOrder
{
    /**
     * @var OrderFactory $orderFactory
     */
    private OrderFactory $orderFactory;
    /**
     * @var ResourceModel $resourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * @param OrderFactory $orderFactory
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        OrderFactory $orderFactory,
        ResourceModel $resourceModel
    ) {
        $this->orderFactory = $orderFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Method to link the order
     *
     * @param string $emailId
     * @param Customer $customer
     * @param string $orderIncrementId
     * @return void
     * @throws AlreadyExistsException
     */
    public function linkOrder($emailId, $customer, $orderIncrementId)
    {
        $order = $this->orderFactory->create();
        $this->resourceModel->load($order, $orderIncrementId, 'increment_id');
        if ($order->getCustomerEmail() === $emailId) {
            $order->setCustomerEmail($emailId);
            $order->setCustomerIsGuest(0);
            $order->setCustomerId($customer->getId());
            $this->resourceModel->save($order);
        }
    }
}
