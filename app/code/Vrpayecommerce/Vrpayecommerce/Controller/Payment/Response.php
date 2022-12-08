<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Response extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * get payment responses
     * @return void
     */
    public function execute()
    {
        $paymentMethod = $this->getRequest()->getParam('payment_method');
        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);

        $repeatedPaypal = $this->getRequest()->getParam('repeated_paypal');
        if ($repeatedPaypal) {
            $orderId = $this->getRequest()->getParam('orderId');
            $this->order = $this->getOrderByIncerementId($orderId);
            $this->processDebitPaypalSaved();
        } else {
            $isServerToServer = $this->getRequest()->getParam('server_to_server');
           $this->processPayment($isServerToServer);
        }
    }

    /**
     * process payment
     * @param  boolean $isServerToServer
     * @return void
     */
    public function processPayment($isServerToServer = false)
    {
        $checkoutId = $this->getRequest()->getParam('id');
        $paymentMethod = $this->paymentMethod;
        $credentials = $paymentMethod->getCredentials();

        if ($isServerToServer == $paymentMethod::SYNCHRONOUS) {
            $paymentStatus = $this->checkoutSession->getInitialPaymentResponse();
        } else {
            $paymentStatus = $this->helperPayment->getPaymentStatus($checkoutId, $credentials, $isServerToServer);
        }

        if(isset($paymentStatus['isValid']) && $paymentStatus['isValid']){

            $returnCode = $paymentStatus['response']['result']['code'];
            $returnMessage = $this->helperPayment->getErrorIdentifier($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($this->isEasyCreditPayment()) {
                $this->quote = $this->getQuote();
                $this->order = $this->quote;
                if (isset($paymentStatus['response']['resultDetails']['Error'])) {
                    $returnMessage = $this->getEasyCreditErrorDetail($paymentStatus['response']);
                    if (isset($returnMessage['field']) && $returnMessage['field'] !== 'null') {
                        $easyCreditErrorDetail = $returnMessage['field'] . ': ' . $returnMessage['renderedMessage'];          
                    } else {            
                        $easyCreditErrorDetail = $returnMessage['renderedMessage'];           
                    }
                    $this->redirectError($easyCreditErrorDetail, $this->checkoutSession->getTransactionId());
                    return false;
                }
            } else {
                if (!isset($paymentStatus['response']['customParameters']['orderId'])) {
                    $orderId = $this->checkoutSession->getOrderId();
                    $this->order = $this->getOrderByIncerementId($orderId);
                    $this->redirectError($returnMessage, $this->checkoutSession->getTransactionId());
                    return false;
                }
                $orderId = $paymentStatus['response']['customParameters']['orderId'];
                $this->order = $this->getOrderByIncerementId($orderId);
            }
            $this->saveOrderAdditionalInformation($paymentStatus['response']);

            if ($transactionResult == 'ACK') {
                if ($this->isEasyCreditPayment()) {
                    $this->_redirect('vrpayecommerce/payment/review', ['_secure' => true]);
                } else {
                    $this->validatePayment($paymentStatus['response']);
                }
            } elseif ($transactionResult == 'NOK') {
                $this->redirectError($returnMessage, $paymentStatus['response']['merchantTransactionId']);
            } elseif ($transactionResult == 'PD' && $paymentStatus['response']['paymentBrand'] == "SOFORTUEBERWEISUNG") {
                $this->order->cancel()->save();
            } else {
                $this->redirectError('ERROR_UNKNOWN', $paymentStatus['response']['merchantTransactionId']);
            }
        } else {
            $orderId = $this->checkoutSession->getOrderId();
            $this->order = $this->getOrderByIncerementId($orderId);
            if(isset($paymentStatus['response'])){
                $this->redirectError($paymentStatus['response'], $this->checkoutSession->getTransactionId());
            } else {
                $this->redirectError('ERROR_GENERAL_NORESPONSE', $this->checkoutSession->getTransactionId());
            }
        }
    }

    /**
     * validate a payment
     * @param  array $paymentStatus
     * @return void
     */
    public function validatePayment($paymentStatus)
    {
        $isRecurringPayment = $this->paymentMethod->isRecurringPayment();
        if ($isRecurringPayment) {
            $this->processRecurringPayment($paymentStatus);
        }

        $this->processSuccessPayment($paymentStatus);
    }

    /**
     * process a success payment
     * @param  array $paymentStatus
     * @return void
     */
    public function processSuccessPayment($paymentStatus)
    {
        $orderSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
        $orderSender->send($this->order);

        $orderStatus = $this->setOrderStatus($paymentStatus);
        if ($orderStatus) {
            $this->order->setState('new')->setStatus($orderStatus)->save();
            $this->order->addStatusToHistory($orderStatus, '', true)->save();
        } else {
            $this->createInvoice();
        }

        $this->deActiveQuote();
        $this->checkoutSession->setLastRealOrderId($this->order->getIncrementId());
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * set an order status
     * @param array $paymentStatus
     */
    protected function setOrderStatus($paymentStatus)
    {
        $isInReview = $this->helperPayment->isSuccessReview($paymentStatus['result']['code']);

        if ($isInReview) {
            $orderStatus = \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod::STATUS_IR;
        } else {
            $orderStatus = false;
            if ($paymentStatus['paymentType'] == 'PA') {
                $orderStatus = \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod::STATUS_PA;
            }
        }
        return $orderStatus;
    }

    /**
     * deactive quote
     * @return void
     */
    protected function deActiveQuote()
    {
        $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
        $quote->loadActive($this->checkoutSession->getLastQuoteId());
        $quote->setReservedOrderId($this->order->getIncrementId());
        $quote->setIsActive(false)->save();
    }

    /**
     * process a payment recurring
     * @param  array &$paymentStatus
     * @return void
     */
    public function processRecurringPayment(&$paymentStatus)
    {
        $success = true;
        if ($this->paymentMethod->getCode() == 'vrpayecommerce_paypalsaved') {
            $registrationId = $paymentStatus['id'];
            $paymentAccount = $paymentStatus['virtualAccount'];
            $paymentBrand = $paymentStatus['paymentBrand'];
            $debitPaypalSaved = $this->processDebitPaypalSaved($registrationId, $paymentStatus['merchantTransactionId']);
            if ($debitPaypalSaved) {
                $paymentStatus = $debitPaypalSaved;
                $paymentStatus['registrationId'] = $registrationId;
                $paymentStatus['paymentBrand'] = $paymentBrand;
                $paymentStatus['virtualAccount'] = $paymentAccount;
            } else {
                $success = false;
            }
        }

        if ($success) {
            $isRegistrationExist =
                $this->information->isRegistrationExist($this->getInformationParamaters(), $paymentStatus['registrationId']);
            if (!$isRegistrationExist) {
                $registrationParameters = array_merge(
                    $this->getInformationParamaters(),
                    $paymentStatus,
                    $this->paymentMethod->getAccount($paymentStatus)
                );
                $this->information->insertRegistration($registrationParameters);
            }
        }
    }

    /**
     * process a debit paypal saved
     * @param  boolean|string $registrationId
     * @param  boolean|string $transactionId
     * @return void
     */
    public function processDebitPaypalSaved($registrationId = false, $transactionId = false)
    {
        if ($registrationId) {
            $referenceId = $registrationId;
        } else {
            $referenceId = $this->getRequest()->getParam('registration_id');
        }

        $paypalSavedParameters = $this->getPaypalSavedParameters($registrationId, $transactionId);
        $debitPaypalSavedResponse = $this->helperPayment->isDebitPaypalGetResponse($referenceId, $paypalSavedParameters);
        if($debitPaypalSavedResponse['isValid']) {
            $returnCode = $debitPaypalSavedResponse['response']['result']['code'];
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);
            $this->saveOrderAdditionalInformation($debitPaypalSavedResponse['response']);
            if ($transactionResult == 'ACK') {
                if ($registrationId) {
                    return $debitPaypalSavedResponse['response'];
                }
                $debitPaypalSavedResponse['response']['registrationId'] = $referenceId;
                $this->processSuccessPayment($debitPaypalSavedResponse['response']);
            } else {
                if ($transactionResult == 'NOK') {
                    $returnMessage = $this->helperPayment->getErrorIdentifier($returnCode);
                } else {
                    $returnMessage = 'ERROR_UNKNOWN';
                }
                $this->redirectError($returnMessage, $debitPaypalSavedResponse['response']['merchantTransactionId']);
            }
        } else{
            $this->order
                    ->getPayment()
                    ->setAdditionalInformation('TRANSACTION_ID', $this->checkoutSession->getTransactionId())
                    ->save();
            $this->redirectError($returnMessage, $debitPaypalSavedResponse['response']);
        }
    }

}
