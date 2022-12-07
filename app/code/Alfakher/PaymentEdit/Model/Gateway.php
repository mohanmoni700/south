<?php

declare(strict_types=1);

namespace Alfakher\PaymentEdit\Model;

use Magento\Payment\Gateway\Command\CommandException;
use ParadoxLabs\FirstData\Model\Gateway as BaseGateway;

class Gateway extends BaseGateway
{
    /**
     * Run an auth transaction for $amount with the given payment info
     *
     * @param object $order
     * @param float $amount
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws CommandException
     */
    public function authorizeBackend($order, $amount)
    {
        $payment = $order->getPayment();
        $this->setParameter('transaction_type', 'authorize');
        $this->setParameter('amount', $amount);
        // Split this logic, if mode is 'Save only' we force an auth with a quote.
        $merchantRef = $order->getIncrementId();
        $currency = $order->getBaseCurrencyCode();
        
        $this->setParameter('merchant_ref', $merchantRef);
        $this->setParameter('currency', $currency);

        if ($payment->hasData('cc_cid') && $payment->getData('cc_cid') != '') {
            $this->setParameter('cvv', $payment->getData('cc_cid'));
        }

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    /**
     * Run a void transaction for the given payment info
     *
     * @param object $order
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws CommandException
     */
    public function voidBackend($order)
    {
        $this->setParameter('transaction_type', 'void');
        $payment     = $order->getPayment();
        
        if ($payment->getLastTransId() != '') {
            $this->setParameter('transId', $payment->getLastTransId());
        }

        if ($payment->getOrigData('base_amount_authorized') !== $payment->getAdditionalInformation('amount')) {
            $newAuthInfo = $payment->getAuthorizationTransaction()->getAdditionalInformation('raw_details_info');
            $this->setParameter('amount', $newAuthInfo['amount']);

            if ($newAuthInfo['reference_transaction_id'] != '') {
                $this->setParameter('transaction_tag', $newAuthInfo['reference_transaction_id']);
            }
        } else {
            if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
                $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
            }
            $this->setParameter('amount', $payment->getAdditionalInformation('amount'));
        }
        $this->setParameter('merchant_ref', $order->getIncrementId());
        $this->setParameter('currency', $order->getBaseCurrencyCode());
        
        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        return $response;
    }

    /**
     * Set parameter for backend.
     *
     * @param number $ccNumber
     * @param number $ccExpMonth
     * @param number $ccExpYear
     * @param number $ccCid
     * @param string $ccType
     * @param string $cardHolderName
     */
    public function setParameterForBackend($ccNumber, $ccExpMonth, $ccExpYear, $ccCid, $ccType, $cardHolderName)
    {
        $this->setParameter('credit_card_type', $ccType);
        $this->setParameter('cardholder_name', $cardHolderName);
        $this->setParameter('card_number', $ccNumber);
        $this->setParameter('exp_date', sprintf('%02d%02d', $ccExpMonth, substr($ccExpYear, -2)));
        $this->setParameter('cvv', $ccCid);
    }

    /**
     * Run a capture transaction for $amount with the given payment info
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @param string $transactionId
     * @return \ParadoxLabs\TokenBase\Model\Gateway\Response
     * @throws CommandException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount, $transactionId = null)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */

        if ($this->getHaveAuthorized()) {
            $this->setParameter('transaction_type', 'capture');

            if ($transactionId !== null) {
                $this->setParameter('transId', $transactionId);
            } else {
                $this->setParameter('transId', $payment->getData('transaction_id'));
            }

            if ($payment->getAdditionalInformation('reference_transaction_id') != '') {
                $this->setParameter('transaction_tag', $payment->getAdditionalInformation('reference_transaction_id'));
            }
        }

        if ($this->getHaveAuthorized() === false || empty($this->getTransactionId())) {
            $this->setParameter('transaction_type', 'purchase');
            $this->setParameter('transaction_tag', null);
        }

        $this->setParameter('amount', $payment->getOrder()->getGrandtotal());
        $this->setParameter('merchant_ref', $payment->getOrder()->getIncrementId());
        $this->setParameter('currency', $payment->getOrder()->getBaseCurrencyCode());

        if ($payment->hasData('cc_cid') && $payment->getData('cc_cid') != '') {
            $this->setParameter('cvv', $payment->getData('cc_cid'));
        }

        $result = $this->createTransaction();
        $response = $this->interpretTransaction($result);

        /**
         * Check for and handle 'transaction not found' error (expired authorization).
         */
        if (in_array('307', $this->getResponseCodes(), false) && $this->getParameter('transId') != '') {
            $this->helper->log(
                $this->code,
                sprintf("Transaction not found. Attempting to recapture.\n%s", json_encode($response->getData()))
            );

            $this->setParameter('transId', null)
                 ->setHaveAuthorized(false)
                 ->setCard($this->getData('card'));

            $response = $this->capture($payment, $amount, '');
        }

        return $response;
    }
}
