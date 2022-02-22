<?php

namespace Alfakher\HandlingFee\Observer;

/**
 *
 */
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $ExtrafeeFee = $quote->getHandlingFee();
        if (!$ExtrafeeFee) {
            return $this;
        }
        //Set handling fee data to order
        $order = $observer->getOrder();
        $order->setData('handling_fee', $ExtrafeeFee);

        return $this;
    }
}
