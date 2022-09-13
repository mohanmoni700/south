<?php

namespace Alfakher\Seamlesschex\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * Execute method
     *
     * @param Observer $observer
     */
    public function execute(
        Observer $observer
    ) {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $order->getPayment()->setAchAccountNumber($quote->getPayment()->getAchAccountNumber());
        $order->getPayment()->setAchRoutingNumber($quote->getPayment()->getAchRoutingNumber());
        $order->getPayment()->setAchCheckNumber($quote->getPayment()->getAchCheckNumber());

        return $this;
    }
}
