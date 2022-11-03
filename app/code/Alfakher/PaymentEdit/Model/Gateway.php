<?php

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
     * @return $this
     * @throws CommandException
     */
    public function setParameterForBackend($ccNumber, $ccExpMonth, $ccExpYear, $ccCid, $ccType, $cardHolderName)
    {
        $this->setParameter('credit_card_type', $ccType);
        $this->setParameter('cardholder_name', $cardHolderName);
        $this->setParameter('card_number', $ccNumber);
        $this->setParameter('exp_date', sprintf('%02d%02d', $ccExpMonth, substr($ccExpYear, -2)));
        $this->setParameter('cvv', $ccCid);
    }
}
