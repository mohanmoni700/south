<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Model\Service\OrderService as OrderServiceSubject;

class OrderService
{
	
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