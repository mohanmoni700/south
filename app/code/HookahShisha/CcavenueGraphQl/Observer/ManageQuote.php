<?php
namespace HookahShisha\CcavenueGraphQl\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ManageQuote implements ObserverInterface
{
    /**
     * Checkout observer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod == 'ccavenue') {
            $quote = $observer->getQuote();
            $quote->setIsActive(true);
            $quote->save();
        }
    }
}
