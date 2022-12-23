<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Information extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * display the my payment information page
     * @return object
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {
            $resultPageFactory = $this->resultPageFactory->create();
            $resultPageFactory->getConfig()->getTitle()->set(__('FRONTEND_MC_INFO'));
    		$block = $resultPageFactory->getLayout()->getBlock('vrpayecommerce_payment_information');

            $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_ccsaved');
            $isRecurringActive = $this->paymentMethod->getGeneralConfig('recurring');
            $block->setIsRecurringActive($isRecurringActive);

            if ($isRecurringActive) {
                $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_ccsaved');
                $isActive = $this->paymentMethod->getconfigData('active');
                $block->setIsCCSavedActive($isActive);
                if ($isActive) {
                    $block->setCustomerDataCC(
                        $this->information->getPaymentInformation($this->getInformationParamaters())
                    );
                }
                $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_ddsaved');
                $isActive = $this->paymentMethod->getconfigData('active');
                $block->setIsDDSavedActive($isActive);
                if ($isActive) {
                    $block->setCustomerDataDD(
                        $this->information->getPaymentInformation($this->getInformationParamaters())
                    );
                }
                $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_paypalsaved');
                $isActive = $this->paymentMethod->getconfigData('active');
                $block->setIsPaypalSavedActive($isActive);
                if ($isActive) {
                    $block->setCustomerDataPaypal(
                        $this->information->getPaymentInformation($this->getInformationParamaters())
                    );
                }

                $block->setRegisterPaymentUrl($this->_url->getUrl('vrpayecommerce/payment/register', ['_secure' => true]));
                $block->setChangePaymentUrl($this->_url->getUrl('vrpayecommerce/payment/change', ['_secure' => true]));
                $block->setDeletePaymentUrl($this->_url->getUrl('vrpayecommerce/payment/delete', ['_secure' => true]));
            }

            return $resultPageFactory;
        }
    }
}
