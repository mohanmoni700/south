<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdditionalInsurance implements ObserverInterface
{

    /**
     * Set Insurance to order from quote
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $order->setComplianceInsurance(
            $observer->getEvent()->getQuote()->getComplianceInsurance()
        );
        $order->setShippingInsurance(
            $observer->getEvent()->getQuote()->getShippingInsurance()
        );

        return $this;
    }
}
