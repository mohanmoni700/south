<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminOrderPlaceAfter implements ObserverInterface
{
	protected $payment;
	protected $sessionQuote;

    /**
     *
     * @param \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment
     * @param \Magento\Backend\Model\Session\Quote              $sessionQuote
     */
    public function __construct(
        \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment,
        \Magento\Backend\Model\Session\Quote $sessionQuote
    ) {
        $this->payment = $payment;
        $this->sessionQuote = $sessionQuote;
        $this->statusPA = \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod::STATUS_PA;
    }

    /**
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$orderEvent = $observer->getEvent()->getOrder();
		$paymentOrder = $orderEvent->getPayment();
		$paymentMethod = $paymentOrder->getMethod();

        $isVrpayecommerceMethod = $this->payment->isVrpayecommerceMethod($paymentMethod);
        if ($isVrpayecommerceMethod) {
            $this->payment->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);

            if ($this->payment->paymentMethod->isAdminLoggedIn()) {
                $this->payment->order = $orderEvent;

                $registrationId = $this->sessionQuote->getVrpayecommerceRegistrationId();
                $this->sessionQuote->unsVrpayecommerceRegistrationId();
                $this->sessionQuote->unsVrpayecommercePaymentMethod();

                $paymentParameters['recurringType'] = 'REPEATED';
            	$paymentParameters = array_merge_recursive(
            		$paymentParameters,
    	            $this->payment->paymentMethod->getCredentials(),
        	        $this->payment->getTransactionParameters(),
                    $this->payment->getCustomParameters()
        	    );

    			$paymentStatus = $this->payment->helperPayment->useRegisteredAccount($registrationId, $paymentParameters);
                
                if($paymentStatus['isValid']){
        	        $returnCode = $paymentStatus['response']['result']['code'];
        	        $returnMessage = __($this->payment->helperPayment->getErrorIdentifier($returnCode));
        	        $transactionResult = $this->payment->helperPayment->getTransactionResult($returnCode);

        			$this->payment->saveOrderAdditionalInformation($paymentStatus['response'], $orderEvent);

        			if ($transactionResult == 'ACK') {
        		        if ($paymentStatus['response']['paymentType'] == 'PA') {
        		            $orderEvent->setState('new')->setStatus($this->statusPA)->save();
        		            $orderEvent->addStatusToHistory($this->statusPA, '', true)->save();
        		        } else {
        		            $this->payment->createInvoice();
        		        }
        		    } elseif ($transactionResult == 'NOK') {
                        throw new \Magento\Framework\Exception\LocalizedException(__($returnMessage));
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(__('ERROR_UNKNOWN'));
                    }
                } else{
                    throw new \Magento\Framework\Exception\LocalizedException(__($paymentStatus['response']));
                }
            }
        }
    }
}
