<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvoiceRegisterObserver implements ObserverInterface
{
    /**
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        $statusDB = \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod::STATUS_DB;
        if (strpos($paymentMethod, 'vrpayecommerce') !== false) {
            $order->setStatus($statusDB);
            $order->addStatusToHistory($statusDB, '', true)->save();
        }
    }
}
