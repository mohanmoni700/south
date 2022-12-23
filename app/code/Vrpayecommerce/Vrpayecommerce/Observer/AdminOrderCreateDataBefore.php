<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminOrderCreateDataBefore implements ObserverInterface
{
	/**
	 *
	 * @param  \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$payment = $observer->getEvent()->getRequestModel()->getPost('payment');
    	$session = $observer->getEvent()->getSession();
	    if (isset($payment['method'])) {
		    $session->setVrpayecommercePaymentMethod($payment['method']);
	    }
	    if (isset($payment['reg_id'])) {
		    $session->setVrpayecommerceRegistrationId($payment['reg_id']);
	    }
    }
}
