<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class DeleteResponse extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * get the responses of payment account deleted
     * @return [type]
     */
    public function execute()
    {
        $informationId = $this->getRequest()->getParam('information_id');
        $paymentMethod = $this->getRequest()->getParam('payment_method');

        if (!isset($informationId) && !isset($paymentMethod)) {
            $this->redirectErrorRecurring('ERROR_MC_DELETE', 'ERROR_GENERAL_REDIRECT');
        }

        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $this->deleteRegistrationByInformationId($informationId);
    }

    /**
     * delete a payment registered by information id
     * @param  string $informationId
     * @return void
     */
    protected function deleteRegistrationByInformationId($informationId)
    {
        $informationParameters = $this->getInformationParamaters();
        $registration = $this->information->getRegistrationByInformationId($informationParameters, $informationId);
        $registrationId = $registration[0]['registration_id'];

        $deleteParameters = $this->paymentMethod->getCredentials();
        $deleteParameters['transactionId'] = $this->customer->getId();

        $deleteStatus = $this->helperPayment->deleteRegistration($registrationId, $deleteParameters);

        if($deleteStatus['isValid']){
            $returnCode = $deleteStatus['response']['result']['code'];
            $errorIdentifier = $this->helperPayment->getErrorIdentifier($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->information->deletePaymentInformationById($informationId);
                $this->redirectSuccessRecurring('SUCCESS_MC_DELETE');
            } elseif ($transactionResult == 'NOK') {
                $this->redirectErrorRecurring('ERROR_MC_DELETE', $errorIdentifier);
            } else {
                $this->redirectErrorRecurring('ERROR_MC_DELETE', 'ERROR_UNKNOWN');
            }
        } else {
            $this->redirectErrorRecurring($deleteStatus['response']);
        }
    }

}
