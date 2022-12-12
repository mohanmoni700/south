<?php

namespace Vrpayecommerce\Vrpayecommerce\Observer;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Vrpayecommerce\Vrpayecommerce\Controller\Payment;
use Vrpayecommerce\Vrpayecommerce\Helper\Payment as PaymentHelper;

class SalesOrderAfterSave implements ObserverInterface
{
    protected $payment;
    protected $paymentHelper;
    protected $sessionQuote;

    /**
     *
     * @param Payment $payment
     * @param PaymentHelper $helperPayment
     */
    public function __construct(
        Payment $payment,
        Quote $sessionQuote,
        PaymentHelper $helperPayment
    ) {
        $this->payment = $payment;
        $this->paymentHelper = $helperPayment;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentOrder = $order->getPayment();
        $paymentMethod = $paymentOrder->getMethod();
        $paymentMethodInstance = $paymentOrder->getMethodInstance();
        
        $isVrpayecommerceMethod = $this->payment->isVrpayecommerceMethod($paymentMethod);

        $this->payment->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);

        if ($isVrpayecommerceMethod && $order instanceof AbstractModel)
        {
            if ($paymentMethodInstance->getConfigData('capture_order'))
            {
                $configuredOrderStates = explode(',', $paymentMethodInstance->getConfigData('capture_order'));
            }
            else
            {
                $configuredOrderStates = [];
            }

            if (($paymentMethodInstance->getConfigData('transaction_mode') === 'PA' || $paymentMethod === 'vrpayecommerce_enterpay' ) &&
                $order->getPayment()->getAdditionalInformation('PAYMENT_TYPE') !== 'CP' &&
                 in_array($order->getStatus(), $configuredOrderStates)
            ) {
                $captureParameters = $this->payment->paymentMethod->getCredentials();

                $captureParameters['paymentType'] = 'CP';

                $captureParameters['amount'] = $order->getPayment()->getAdditionalInformation('AMOUNT');

                $captureParameters['currency'] = $order->getPayment()->getAdditionalInformation('CURRENCY');

                $captureStatus = $this->paymentHelper->backOfficeOperation($order->getPayment()->getAdditionalInformation('REFERENCE_ID'), $captureParameters);

                if($captureStatus['isValid'])
                {
                    $resultCode = $captureStatus['response']['result']['code'];
                    $returnMessage = __($this->payment->helperPayment->getErrorIdentifier($resultCode));
                    $transactionResult = $this->paymentHelper->getTransactionResult($resultCode);

                    if ($transactionResult == 'ACK')
                    {
                        $paymentOrder->setAdditionalInformation('PAYMENT_TYPE', 'CP')->save();
                    }
                    elseif ($transactionResult == 'NOK')
                    {
                        throw new LocalizedException(__($returnMessage));
                    }
                    else
                    {
                        throw new LocalizedException(__('ERROR_UNKNOWN'));
                    }
                }
                else
                {
                    throw new LocalizedException(__($captureStatus['response']));
                }
            }
        }

        return $this;
    }
}


