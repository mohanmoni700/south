<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Delete extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * delete a payment account
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
            $deleteInformation =
                $this->information->getRegistrationByInformationId($informationParameters, $informationId);

            if (!$deleteInformation) {
                $this->redirectErrorRecurring('ERROR_GENERAL_REDIRECT');
            }

            return $this->getResultPageFactory($informationId, $deleteInformation);
        }
    }

    /**
     * display the page to delete a payment account
     * @param  string $informationId
     * @param  string $deleteInformation
     * @return object
     */
    protected function getResultPageFactory($informationId, $deleteInformation)
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('FRONTEND_MC_DELETE'));

        $block = $resultPageFactory->getLayout()->getBlock('vrpayecommerce_payment_delete');

        $cancelUrl = $this->_url->getUrl('vrpayecommerce/payment/information', ['_secure' => true]);
        $responseUrl = $this->_url->getUrl('vrpayecommerce/payment/deleteresponse', ['_secure' => true]);

        $block->setCancelUrl($cancelUrl);
        $block->setResponseUrl($responseUrl);
        $block->setInformationId($informationId);
        $block->setPaymentMethod($this->paymentMethod->getCode());
        $block->setDeleteInformation($deleteInformation);

        return $resultPageFactory;
    }
}
