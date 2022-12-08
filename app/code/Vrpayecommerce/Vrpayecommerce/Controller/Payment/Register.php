<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Register extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * register a payment account in my payment information page
     * @return void
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {

    		$paymentMethod = $this->getRequest()->getParam('payment_method');

            if (!isset($paymentMethod)) {
                $this->redirectErrorRecurring('ERROR_GENERAL_REDIRECT');
            }

            $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
			$recurringParameters = $this->getRecurringParameters();

            $recurringParameters = $this->paymentMethod->getTrackingDataForCheckoutResult($recurringParameters);

        	$checkoutResult = $this->helperPayment->getCheckoutResult($recurringParameters);
            
            if(!$checkoutResult['isValid']){
                $this->redirectErrorRecurring($checkoutResult['response']);
            } elseif (!isset($checkoutResult['response']['id'])) {
                $this->redirectErrorRecurring('ERROR_GENERAL_REDIRECT');
            } else{
                $paymentWidgetUrl  = $this->helperPayment->getPaymentWidgetUrl(
                    $recurringParameters['serverMode'],
                    $checkoutResult['response']['id']
                    );
                return $this->getResultPageFactory($paymentWidgetUrl);
            }
        }
    }

    /**
     * display a payment widget form to register a payment account
     * @param  string $paymentWidgetUrl
     * @return object
     */
    protected function getResultPageFactory($paymentWidgetUrl)
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('FRONTEND_MC_SAVE'));

        $this->setPageAssetRecurring($resultPageFactory);

        $block = $resultPageFactory->getLayout()->getBlock('vrpayecommerce_payment_register');

        $cancelUrl = $this->_url->getUrl('vrpayecommerce/payment/information', ['_secure' => true]);
        $responseUrl = $this->_url->getUrl('vrpayecommerce/payment/recurringresponse',
            ['payment_method' => $this->paymentMethod->getCode(), '_secure' => true]
        );

        $block->setBrand($this->paymentMethod->getBrand());
        $block->setLang($this->paymentMethod->getLangCode());
        $block->setWidgetStyle($this->paymentMethod->getWidgetStyle());
        $block->setPaymentCode($this->paymentMethod->getCode());
        $block->setTestMode($this->paymentMethod->getTestMode());
        $block->setCancelUrl($cancelUrl);
        $block->setResponseUrl($responseUrl);
        $block->setPaymentWidgetUrl($paymentWidgetUrl);

        return $resultPageFactory;
    }
}
