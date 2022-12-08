<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Change extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * change a payment account
     * @return void
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {
    		$informationId = $this->getRequest()->getParam('information_id');
    		$paymentMethod = $this->getRequest()->getParam('payment_method');

    		if (!isset($informationId) && !isset($paymentMethod)) {
	            $this->redirectErrorRecurring('ERROR_GENERAL_REDIRECT');
	        }

            $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
            $informationParameters = $this->getInformationParamaters();
            $registration = $this->information->getRegistrationByInformationId($informationParameters, $informationId);
            $recurringParameters = $this->getRecurringParameters($registration[0]['registration_id']);

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
                return $this->getResultPageFactory($informationId, $paymentWidgetUrl);
            }
        }
    }

    /**
     * display a payment widget form to change a payment account
     * @param  string $informationId
     * @param  string $paymentWidgetUrl
     * @return object
     */
    protected function getResultPageFactory($informationId, $paymentWidgetUrl)
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('FRONTEND_MC_CHANGE'));

        $this->setPageAssetRecurring($resultPageFactory);

        $block = $resultPageFactory->getLayout()->getBlock('vrpayecommerce_payment_change');

        $cancelUrl = $this->_url->getUrl('vrpayecommerce/payment/information', ['_secure' => true]);
        $responseUrl = $this->_url->getUrl('vrpayecommerce/payment/recurringresponse',
            [
                'payment_method' => $this->paymentMethod->getCode(),
                'information_id' => $informationId,
                '_secure' => true
            ]
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
