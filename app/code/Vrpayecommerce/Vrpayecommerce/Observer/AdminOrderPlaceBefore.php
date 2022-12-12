<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminOrderPlaceBefore implements ObserverInterface
{
	protected $payment;

    /**
     *
     * @param \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment
     */
    public function __construct(
        \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment
    ) {
        $this->payment = $payment;
    }

    /**
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$order = $observer->getEvent()->getOrder();
		$payment = $order->getPayment();
		$paymentMethod = $payment->getMethod();

        $isVrpayecommerceMethod = $this->payment->isVrpayecommerceMethod($paymentMethod);
        if ($isVrpayecommerceMethod) {
            $this->payment->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);
            $this->payment->_order = $order;
        }
    }
}
