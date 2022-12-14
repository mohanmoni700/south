<?php
declare (strict_types = 1);

namespace HookahShisha\SubscribeGraphQl\Model\Magedelight\Subscribenow\Service;

use Magedelight\Subscribenow\Model\Service\OrderService as SubOrderService;

class OrderService extends SubOrderService
{
    public function setOrderInfo($order, $item)
    {
        $this->getSubscriptionModel()->setSubscriptionStatus(1);
        $this->getSubscriptionModel()->setInitialOrderId($order->getIncrementId());

        $customerBillingAddressId = $order->getBillingAddress()->getCustomerAddressId();
        $customerShippingAddressId = 0;
        if (!$item->getIsVirtual()) {
            $customerShippingAddressId = $order->getShippingAddress()->getCustomerAddressId();
        }

        $billingAddressId = $customerBillingAddressId ?: $customerShippingAddressId;
        if (!$billingAddressId) {
            $addresses = $order->getAddresses();
            if ($addresses && !empty($addresses[0])) {
                $billingAddressId = $addresses[0]['customer_address_id'];
            }
        }

        $this->getSubscriptionModel()->setBillingAddressId($billingAddressId);
        if (!$item->getIsVirtual()) {
            $shippingAddressId = $customerShippingAddressId ?: $billingAddressId;
            $this->getSubscriptionModel()->setShippingAddressId($shippingAddressId);
        }

        $this->getSubscriptionModel()->setBaseCurrencyCode($order->getBaseCurrencyCode());
        $this->getSubscriptionModel()->setCurrencyCode($order->getOrderCurrencyCode());

        $this->getSubscriptionModel()->setCustomerId($order->getCustomerId());
        $this->getSubscriptionModel()->setSubscriberName($order->getCustomerName());
        $this->getSubscriptionModel()->setSubscriberEmail($order->getCustomerEmail());

        $this->getSubscriptionModel()->setStoreId($order->getStoreId());

        $this->getSubscriptionModel()->setBaseShippingAmount($order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount());
        $this->getSubscriptionModel()->setShippingAmount($order->getShippingAmount() + $order->getShippingTaxAmount());
        $this->getSubscriptionModel()->setOrderIncrementId($order->getIncrementId());

        /* add billing and shipping address info */
        $this->getSubscriptionModel()->setShippingAddressInfo(json_encode($order->getShippingAddress()->getData()));
        $this->getSubscriptionModel()->setBillingAddressInfo(json_encode($order->getBillingAddress()->getData()));
        /* -- add billing and shipping address info -- */

        return $this;
    }
}
