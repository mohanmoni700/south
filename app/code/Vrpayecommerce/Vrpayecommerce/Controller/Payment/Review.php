<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Review extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * Review order after returning from easyCredit
     *
     * @return void
     */
    public function execute()
    {
        $this->quote = $this->getQuote();
        $this->order = $this->quote;
        $this->validateQuote();
        $this->validatePaymentAdditonalInformation();
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Order Review'));
        $this->_view->renderLayout();
    }

    /**
     * validate quote
     *
     * @return void
     */
    protected function validateQuote()
    {
        if (!$this->quote->hasItems() || $this->quote->getHasError()) {
            $this->redirectError('ERROR_GENERAL_UNAVAIABLE_CHECKOUT_REVIEW');
        }
    }

    /**
     * validate payment additional information
     *
     * @return void
     */
    protected function validatePaymentAdditonalInformation()
    {
        $payment = $this->quote->getPayment();

        if ($payment->getAdditionalInformation('redemption_plan') == null ||
            $payment->getAdditionalInformation('pre_contract_information_url') == null ||
            $payment->getAdditionalInformation('easycredit_sum_of_interest') == null ||
            $payment->getAdditionalInformation('easycredit_order_total') == null
        ) {
            $this->redirectError('ERROR_GENERAL_UNAVAIABLE_CHECKOUT_REVIEW');
        }
    }

}
