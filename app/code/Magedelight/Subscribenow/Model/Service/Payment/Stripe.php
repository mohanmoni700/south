<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service\Payment;

class Stripe extends AbstractPayment
{
    /**
     * Change the constant values
     * as per payment method class
     */
    const SUBSCRIPTION_ID = 'md_stripe_card_id';
    const CARD_ID_COLUMN = 'stripe_customer_id';
    const CARD_CC_NUMBER_COLUMN = 'cc_last_4';

    /**
     * Get Payment Token
     * @return string
     */
    public function getPaymentToken()
    {
        $token = $this->getAdditionalInformation(self::SUBSCRIPTION_ID);
        return ($token) ? $this->encryptor->encrypt($token) : null;
    }

    private function getCardNumber($row)
    {
        return sprintf(self::CARD_PREFIX, $row->getData(self::CARD_CC_NUMBER_COLUMN));
    }

    public function getCardInfo()
    {
        $cardDigit = null;
        $token = $this->encryptor->decrypt($this->data['token']);
        $collection = $this->getCollection(self::CARD_ID_COLUMN, $token);

        if ($collection->getSize()) {
            /** Due to MEQP2 getFirstItem warning we use foreach */
            foreach ($collection as $row) {
                $cardDigit = $this->getCardNumber($row);
                break;
            }
        }

        return $cardDigit;
    }

    public function getSavedCards()
    {
        $cards = [];
        $customerId = $this->data['customer_id'];
        $collection = $this->getCollection('customer_id', $customerId);
        $decryptedToken = $this->encryptor->decrypt($this->data['token']);

        if ($collection->getSize()) {
            foreach ($collection as $card) {
                $token = $card->getData(self::CARD_ID_COLUMN);
                $cardInfo = $this->getCardNumber($card);

                $isCurrentCard = ($decryptedToken == $token) ? true : false;

                $cards[$this->encryptor->encrypt($token)] = [
                    'is_current' => $isCurrentCard,
                    'label' => $cardInfo
                ];
            }
        }

        return $cards;
    }

    public function getImportData()
    {
        /** @var \Magedelight\Subscribenow\Model\ProductSubscribers $subscription */
        $subscription = $this->data['subscription_instance'];

        return [
            'method' => 'md_stripe_cards',
            'md_stripe_card_id' => $this->encryptor->decrypt($subscription->getPaymentToken()),
            'save_card' => 'true'
        ];
    }
}
