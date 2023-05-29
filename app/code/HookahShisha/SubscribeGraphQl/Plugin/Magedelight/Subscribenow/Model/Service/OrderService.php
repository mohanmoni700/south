<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Model\Service\OrderService as OrderServiceSubject;

/**
 * OrderService
 */
class OrderService
{
    /**
     * After set order info plugin
     *
     * @param OrderServiceSubject $subject
     * @param object $result
     * @param object $order
     * @param object $item
     */
    public function afterSetOrderInfo(
        OrderServiceSubject $subject,
        $result,
        $order,
        $item
    ) {
        $result->getSubscriptionModel()->setShippingAddressInfo(json_encode($order->getShippingAddress()->getData()));
        $result->getSubscriptionModel()->setBillingAddressInfo(json_encode($order->getBillingAddress()->getData()));
        return $result;
    }
}
