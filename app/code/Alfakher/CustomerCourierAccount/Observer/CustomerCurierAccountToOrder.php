<?php

namespace Alfakher\CustomerCourierAccount\Observer;

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerCurierAccountToOrder implements ObserverInterface
{

    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * Construct
     *
     * @param Copy $objectCopyService
     */
    public function __construct(
        Copy $objectCopyService
    ) {
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        $order->setData('customer_courier_name', $quote->getData('customer_courier_name'));
        $order->setData('customer_courier_account', $quote->getData('customer_courier_account'));

        return $this;
    }
}
