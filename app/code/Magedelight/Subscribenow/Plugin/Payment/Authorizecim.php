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

namespace Magedelight\Subscribenow\Plugin\Payment;

class Authorizecim
{

    /**
     * @var Validate
     */
    private $validate;

    /**
     * @param Validate $validate
     */
    public function __construct(Validate $validate)
    {
        $this->validate = $validate;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $response
     * @param $payment
     * @param $customerId
     * @return mixed
     */
    public function aroundSaveCustomerProfileData($subject, $proceed, $response, $payment, $customerId)
    {
        $result = $proceed($response, $payment, $customerId);
        if ($this->validate->hasSubscriptionProduct()) {
            $subscriptionId = $response->customerPaymentProfileIdList->numericString;
            if (is_object($subscriptionId)) {
                $subscriptionId = (string) $subscriptionId;
            }
            $payment->setAdditionalInformation('md_payment_profile_id', $subscriptionId);
        }
        return $result;
    }

    public function aroundPrepareProfileResponse($subject, $proceed, $payment, $customerProfileId, $transId)
    {
        $result = $proceed($payment, $customerProfileId, $transId);
        if ($this->validate->hasSubscriptionProduct()) {
            $payment->setAdditionalInformation('md_payment_profile_id', $result['payment_profile_id']);
        }
        return $result;
    }
}
