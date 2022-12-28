<?php
declare(strict_types=1);

namespace Alfakher\PaymentMethod\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Splitit\PaymentGateway\Gateway\Response\TxnIdHandler;

class SplititOrderStatusChange implements ObserverInterface
{

    /**
     * SplitIt Payment method order status change
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
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
