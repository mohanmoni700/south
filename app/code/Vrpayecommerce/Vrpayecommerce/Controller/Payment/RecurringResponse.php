<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class RecurringResponse extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * get payment response in the my payment information page
     * @return void
     */
    public function execute()
    {
        $checkoutId = $this->getRequest()->getParam('id');
        $informationId = $this->getRequest()->getParam('information_id');
        $paymentMethod = $this->getRequest()->getParam('payment_method');

        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);

        $credentials = $this->paymentMethod->getCredentials();
        $paymentStatus = $this->helperPayment->getPaymentStatus($checkoutId, $credentials);

        if (!$paymentStatus['isValid']) {
            $this->redirectErrorRecurring(null, $paymentStatus['response'], $informationId);
        } else {
            $returnCode = $paymentStatus['response']['result']['code'];
            $errorIdentifier = $this->helperPayment->getErrorIdentifier($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->processPaymentSuccessRecurring($informationId, $paymentStatus['response']);
            } elseif ($transactionResult == 'NOK') {
                $this->redirectErrorRecurring(null, $errorIdentifier, $informationId);
            } else {
                $this->redirectErrorRecurring(null, 'ERROR_UNKNOWN', $informationId);
            }
        }
    }

    /**
     * process the payment success recurring
     * @param  string $informationId
     * @param  array $paymentStatus
     * @return void
     */
    protected function processPaymentSuccessRecurring($informationId, $paymentStatus)
    {
        $registrationParameters = $this->paymentMethod->getCredentials();
        $registrationParameters['amount'] = $this->paymentMethod->getRegisterAmount();
        $registrationParameters['currency'] = $this->getRecurringCurrency();
        $registrationParameters['transactionId'] = $paymentStatus['merchantTransactionId'];
        $registrationParameters['recurringType'] = 'INITIAL';

        if( $this->paymentMethod->getCode() == "vrpayecommerce_paypalsaved" ) {
            $success = $this->processPayPalSavedRecurring($informationId, $registrationParameters, $paymentStatus);
        } else {
            $success = $this->processPaymentSavedRecurring($informationId, $registrationParameters, $paymentStatus);
        }

        if ($success) {
            if ($informationId) {
                $registrationId = $paymentStatus['merchantTransactionId'];
                $this->helperPayment->deleteRegistration($registrationId, $registrationParameters);
                $this->redirectSuccessRecurring('SUCCESS_MC_UPDATE');
            } else {
                $this->redirectSuccessRecurring('SUCCESS_MC_ADD');
            }
        }
    }

    /**
     * process the PayPal recurring
     * @param  string $informationId
     * @param  array $registrationParameters
     * @param  array $paymentStatus
     * @return boolean
     */
    protected function processPayPalSavedRecurring($informationId, $registrationParameters, $paymentStatus)
    {
        $registrationId = $paymentStatus['id'];
        $registrationParameters['paymentType'] = $this->paymentMethod->getPaymentType();

        $debitStatus = $this->helperPayment->useRegisteredAccount($registrationId, $registrationParameters);

        if($debitStatus['isValid']){
            $returnCode = $debitStatus['response']['result']['code'];
            $errorIdentifier = $this->helperPayment->getErrorIdentifier($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $referenceId = $debitStatus['response']['id'];
                $registrationParameters['paymentType'] = 'RF';
                $this->helperPayment->backOfficeOperation($referenceId, $registrationParameters);
                $this->savePaymentAccount($informationId, $registrationId, $paymentStatus);
            } else {
                $this->helperPayment->deleteRegistration($registrationId, $registrationParameters);
                if ($transactionResult == 'NOK') {
                    $this->redirectErrorRecurring(null, $errorIdentifier, $informationId);
                    return false;
                } else {
                    $this->redirectErrorRecurring(null, 'ERROR_UNKNOWN', $informationId);
                    return false;
                }
            }
            return true;
        } else {
            $this->redirectErrorRecurring(null, $debitStatus['response'], $informationId);
        }
    }

    /**
     * process the credit cards and direct debit payment recurring
     * @param  string $informationId
     * @param  array $registrationParameters
     * @param  array $paymentStatus
     * @return boolean
     */
    protected function processPaymentSavedRecurring($informationId, $registrationParameters, $paymentStatus)
    {
        $registrationId = $paymentStatus['registrationId'];
        $referenceId = $paymentStatus['id'];
        $this->captureRecurringPayment($registrationId, $referenceId, $informationId, $registrationParameters, $paymentStatus);
        $registrationParameters['paymentType'] = 'RF';
        $this->helperPayment->backOfficeOperation($referenceId, $registrationParameters);
        $this->savePaymentAccount($informationId, $registrationId, $paymentStatus);
        return true;
    }

    /**
     * capture payment when order status is PA(Pre-Authorization)
     * @param  string $informationId
     * @param  array $registrationParameters
     * @param  array $paymentStatus
     * @return boolean
     */
    protected function captureRecurringPayment($registrationId, $referenceId, $informationId, $registrationParameters, $paymentStatus)
    {
        if ($paymentStatus['paymentType'] == 'PA') {
            $registrationParameters['paymentType'] = 'CP';
            $captureStatus = $this->helperPayment->backOfficeOperation($referenceId, $registrationParameters);
            if($captureStatus['isValid']){
                $returnCode = $captureStatus['response']['result']['code'];
                $errorIdentifier = $this->helperPayment->getErrorIdentifier($returnCode);
                $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

                if ($transactionResult == 'ACK') {
                    $referenceId = $captureStatus['response']['id'];
                } else {
                    $this->helperPayment->deleteRegistration($registrationId, $registrationParameters);
                    if ($transactionResult == 'NOK') {
                        $this->redirectErrorRecurring(null, $errorIdentifier, $informationId);
                    } else {
                        $this->redirectErrorRecurring(null, 'ERROR_UNKNOWN', $informationId);
                    }
                }
            } else {
                $this->redirectErrorRecurring(null, $captureStatus['response'], $informationId);
            }
        }
    }
    /**
     * save a payment account
     * @param  string $informationId
     * @param  string $registrationId
     * @param  array $paymentStatus
     * @return void
     */
    protected function savePaymentAccount($informationId, $registrationId, $paymentStatus)
    {
        $registrationParameters = array_merge(
            $this->getInformationParamaters(),
            $this->paymentMethod->getAccount($paymentStatus)
        );
        $registrationParameters['paymentBrand'] = $paymentStatus['paymentBrand'];
        $registrationParameters['registrationId'] = $registrationId;

        if ($informationId) {
            $this->information->updateRegistration($registrationParameters, $informationId);
        } else {
            $this->information->insertRegistration($registrationParameters);
        }
    }

}
