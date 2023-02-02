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

class Authorizecim extends AbstractPayment
{
    /**
     * Change the constant values
     * as per payment method class
     */
    const SUBSCRIPTION_ID = 'md_payment_profile_id';
    const CARD_ID_COLUMN = 'payment_profile_id';
    const CARD_CC_NUMBER_COLUMN = 'cc_last_4';
    const SUCCESS_RESPONSE_CODE = 'I00001';
    const SUCCESS_RESULT_STRING = 'OK';

    /**
     * Get Payment Token
     * @return string
     */
    public function getPaymentToken()
    {
        $token = $this->getAdditionalInformation(self::SUBSCRIPTION_ID);
        return ($token) ? $this->encryptor->encrypt($token) : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getCardNumber($row)
    {
        return sprintf(self::CARD_PREFIX, $row->getData(self::CARD_CC_NUMBER_COLUMN));
    }

    /**
     * Get Card Information
     * @return null|string
     */
    public function getCardInfo()
    {
        $token = $this->encryptor->decrypt($this->data['token']);
        $collection = $this->getCollection(self::CARD_ID_COLUMN, $token);

        if ($collection->getSize()) {
            return $this->getCardNumber($collection->getFirstItem());
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
        $collection = $this->getCollection('customer_id', $customerId);

        if ($collection->getSize()) {
            foreach ($collection as $card) {
                $token = $this->encryptor->encrypt($card->getData(self::CARD_ID_COLUMN));
                $cardInfo = $this->getCardNumber($card);

                $label = $cardInfo . ', ' . $card->getData('firstname') . ' ' . $card->getData('lastname');
                $isCurrentCard = ($this->encryptor->decrypt($this->data['token']) == $card->getData(self::CARD_ID_COLUMN)) ? 1 : 0;

                $cards[$token] = [
                    'is_current' => $isCurrentCard,
                    'label' => $label
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
            'method' => 'md_authorizecim',
            'save_card' => 'true',
            'md_payment_profile_id' => $subscription->getPaymentToken(),
            'payment_profile_id' => $subscription->getPaymentToken()
        ];
    }
}
