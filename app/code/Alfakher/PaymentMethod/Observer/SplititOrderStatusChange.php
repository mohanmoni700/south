<?php

namespace Alfakher\PaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;
use Splitit\PaymentGateway\Gateway\Response\TxnIdHandler;

class SplititOrderStatusChange implements ObserverInterface
{

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentOrder = $order->getPayment();
        $paymentMethod = $paymentOrder->getMethod();
        if ($paymentMethod == TxnIdHandler::PAYMENT_METHOD) {
            $order->setState("pending")->setStatus("pending");
            $order->save();
        }
    }
}
