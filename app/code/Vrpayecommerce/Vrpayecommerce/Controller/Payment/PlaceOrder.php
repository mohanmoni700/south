<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class PlaceOrder extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * Place order after returning from easyCredit
     *
     * @return void
     */
    public function execute()
    {
        $reviewButton = $this->getRequest()->getParam('review_button');
        if (isset($reviewButton) && $reviewButton == 'cancel') {
            $this->_redirect('checkout/cart', ['_secure' => true]);
        } else {
            $this->quote = $this->getQuote();
            $payment = $this->quote->getPayment();
            $this->paymentMethod = $payment->getMethodInstance();

            $paymentType = 'CP';
            $currency = $payment->getAdditionalInformation('CURRENCY');
            $amount = $payment->getAdditionalInformation('AMOUNT');
            $transactionId = $payment->getAdditionalInformation('TRANSACTION_ID');
            $referenceId = $payment->getAdditionalInformation('REFERENCE_ID');
            $grandTotal = $this->getQuote()->getGrandTotal();

            $captureParameters =  $this->paymentMethod->getCredentials();
            $captureParameters['amount'] = $amount;
            $captureParameters['currency'] = $currency;
            $captureParameters['paymentType'] = $paymentType;

            $captureStatus = $this->helperPayment->backOfficeOperation($referenceId, $captureParameters);

            if ($this->isOrderValid($currency, $amount, $referenceId)) {
                if($captureStatus['isValid']){                
                    $resultCode = $captureStatus['response']['result']['code'];
                    $transactionResult = $this->helperPayment->getTransactionResult($resultCode);

                    if ($transactionResult == 'ACK') {
                        $payment->setAdditionalInformation('PAYMENT_TYPE', $paymentType)->save();
                        $cartId = $this->quote->getId();
                        $quoteManagement = $this->_objectManager->create('Magento\Quote\Model\QuoteManagement');
                        $orderId = $quoteManagement->placeOrder($cartId);
                        $this->order = $this->getOrderById($orderId);

                        $orderSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
                        $orderSender->send($this->order);
                        $this->createInvoice();

                        $this->_redirect('checkout/onepage/success');
                    } elseif ($transactionResult == 'NOK') {
                        $returnMessage = $this->helperPayment->getErrorIdentifierBackend($resultCode);
                        $this->redirectError($returnMessage, $transactionId);
                    } else {
                        $this->redirectError('ERROR_UNKNOWN', $transactionId);
                    }
                } else {
                    $this->redirectError($captureStatus['response']);
                }
            } else {
                    if($captureStatus['response']['amount'] != $grandTotal){
                        $this->redirectError('ERROR_GENERAL_CAPTURE_PAYMENT');
                    }else{
                        $this->redirectError('ERROR_ORDER_INVALID');
                    }
            }
        }
    }

    /**
     * check if order valid or not
     *
     * @param  string $currency
     * @param  string $amount
     * @param  string $referenceId
     * @return bool
     */
    protected function isOrderValid($currency, $amount, $referenceId)
    {
        $valid = false;
        if (isset($currency) && isset($amount) && isset($referenceId)) {
            if ($currency == $this->quote->getQuoteCurrencyCode() &&
                $amount == $this->helperPayment->setNumberFormat($this->quote->getGrandTotal())
            ) {
                $valid = true;
            }
        }
        return $valid;
    }

}
