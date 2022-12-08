<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class PaypalSaved extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_paypalsaved';
    protected $brand = 'PAYPAL';
    protected $accountType = 'virtualAccount';
    protected $methodTitle = 'FRONTEND_MC_PM_PAYPALSAVED';
    protected $adminMethodTitle = 'BACKEND_PM_PAYPALSAVED';
    protected $paymentCode = 'VA';
    protected $paymentGroup = 'PAYPAL';
    protected $isRecurringPayment = true;
    protected $logo = 'paypal.png';

    /**
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return boolean
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isAvailable = parent::isAvailable($quote);

        if ($isAvailable) {
            $customerSession = $this->createCustomerSessionObject();
            if (!$this->isAdminLoggedIn() && (!$this->isRecurring() || !$customerSession->isLoggedIn())) {
                return false;
            }
        }

        return $isAvailable;
    }

    /**
     * get a payment type
     * @return string
     */
    public function getPaymentType()
    {
        return $this->getPaymentTypeSelection();
    }

    /**
     * get account
     * @param  array $paymentStatus
     * @return array
     */
    public function getAccount($paymentStatus)
    {
        $account = array();
        $account = $paymentStatus[$this->accountType];
        $account['email'] = $account['accountId'];
        $account['last4Digits'] = '';
        $account['expiryMonth'] = '';
        $account['expiryYear'] = '';

        return $account;
    }
}
