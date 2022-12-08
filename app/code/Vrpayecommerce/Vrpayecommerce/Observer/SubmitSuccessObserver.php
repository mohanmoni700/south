<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Framework\Event\ObserverInterface;

class SubmitSuccessObserver implements ObserverInterface
{
	protected $authSession;

    /**
     *
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->authSession = $authSession;
    }

    /**
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$isAdminLoggedIn =  $this->authSession->isLoggedIn();
    	if (!$isAdminLoggedIn) {
            $quote = $observer->getEvent()->getQuote();
            $paymentMethod = $quote->getPayment()->getMethod();
            if (strpos($paymentMethod, 'vrpayecommerce') !== false) {
                if ($paymentMethod !== 'vrpayecommerce_easycredit') {
        	        $quote->setIsActive(true);
        	        $quote->setReservedOrderId(null);
                }
            }
	    }
    }
}
