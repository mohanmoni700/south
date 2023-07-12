<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Magento\SalesGraphQl\Model\Formatter;

use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociateOrders;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Order
 */
class Order
{
    /**
     * @var AssociateOrders
     */
    protected AssociateOrders $associateOrders;

    /**
     * @param AssociateOrders $associateOrders
     */
    public function __construct(
        AssociateOrders $associateOrders
    ) {
        $this->associateOrders = $associateOrders;
    }

    /**
     * Set subscription id with the customer's order data
     *
     * @param OrderFormatter $subject
     * @param array $return
     * @param OrderInterface $orderModel
     * @return array
     */
    public function afterFormat(
        OrderFormatter $subject,
        $return,
        OrderInterface $orderModel
    ) {
        $profiles = $this->getAssociateSubscription($orderModel->getIncrementId());
        if (count($profiles)) {
            foreach ($profiles as $profileIds) {
                $return["orderSubscriptionDetails"][] = [
                    "profile_id" => $profileIds['profile_id'],
                    "subscription_id" => $profileIds['subscription_id'],
                ];
            }
        }
        return $return;
    }

    /**
     * Get subscription ids
     *
     * @param String $orderNumber
     * @return array
     */
    public function getAssociateSubscription($orderNumber): array
    {
        $collection = $this->associateOrders->create()
            ->addFieldToFilter('order_id', $orderNumber);
        $collection->getSelect()->joinLeft(
            ['prof' => $collection->getTable('md_subscribenow_product_subscribers')],
            'main_table.subscription_id = prof.subscription_id',
            ['prof.subscription_id', 'prof.profile_id']
        );
        return ($collection->getSize() > 0) ? $collection->getData() : [];
    }
}
