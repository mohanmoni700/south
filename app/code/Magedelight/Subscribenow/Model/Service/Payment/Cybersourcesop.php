<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service\Payment;

use Magento\Vault\Api\Data\PaymentTokenInterface;

class Cybersourcesop extends AbstractPayment
{
    /**
     * Return Payment token
     *
     * @return string
     */
    public function getPaymentToken()
    {
        $additionalInfo = $this->getAdditionalInformation();
        if ($this->getMethodCode() == 'cybersourcesop') {
            if (isset($additionalInfo['public_hash'])) {
                return $additionalInfo['public_hash'];
            } elseif (isset($additionalInfo['payment_token'])) {
                $token = $additionalInfo['payment_token'];
                $customerId = $this->order->getCustomerId();
                $vaultToken = $this->tokenManagement->getByGatewayToken($token, 'cybersourcesop', $customerId);
                if ($vaultToken != null) {
                    return $vaultToken->getPublicHash();
                }
            }
        }
        return;
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    protected function generatePublicHash($paymentToken, $customerid)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * Get Active Card Number
     * @return string
     */
    public function getCardInfo()
    {
        $publicHash = $this->data['token'];
        $customerId = $this->data['customer_id'];
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if ($paymentToken) {
            $activeflag = $paymentToken->getIsActive();
            if ($activeflag) {
                $details = json_decode($paymentToken->getDetails(), true);
                return $details['maskedCC'];
            }
        }
        return null;
    }

    /**
     * Get Saved Cards
     * @return array
     */
    public function getSavedCards()
    {
        $cards = [];
        $customerId = $this->data['customer_id'];
        if ($customerId) {
            $customers = $this->tokenManagement->getListByCustomerId($customerId);
            foreach ($customers as $card) {
                if ($card->getPaymentMethodCode() == 'cybersourcesop' && $card->getIsActive()) {
                    $detail = json_decode($card->getDetails(), true);
                    $token = $card->getPublicHash();
                    $isCurrentCard = ($this->data['token'] == $token) ? 1 : 0;
                    $cardInfo = $detail['maskedCC'];
                    $cards[$token] = [
                        'is_current' => $isCurrentCard,
                        'label' => $cardInfo
                    ];
                }
            }
        }
        return $cards;
    }

    public function getImportData()
    {
        /** @var \Magedelight\Subscribenow\Model\ProductSubscribers $subscription */
        $subscription = $this->data['subscription_instance'];
        return [
            'method' => 'cybersourcesop_cc_vault',
            'is_active_payment_token_enabler' => 'true',
            'public_hash' => $subscription->getPaymentToken()
        ];
    }
}
