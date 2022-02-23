<?php

namespace Alfakher\HandlingFee\Observer;

/**
 * Add handling fee to order
 *
 * @author af_bv_op
 */
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
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
