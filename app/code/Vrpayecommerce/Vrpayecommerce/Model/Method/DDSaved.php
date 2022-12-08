<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class DDSaved extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_ddsaved';
    protected $brand = 'DIRECTDEBIT_SEPA';
    protected $accountType = 'bankAccount';
    protected $methodTitle = 'FRONTEND_MC_PM_DDSAVED';
    protected $adminMethodTitle = 'BACKEND_PM_DD_SAVED';
    protected $paymentCode = 'DD';
    protected $paymentGroup = 'DD';
    protected $isRecurringPayment = true;
    protected $logo = 'sepa.png';

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
        $account['email'] = '';
        $account['last4Digits'] = substr($account['iban'],-4);
        $account['expiryMonth'] = '';
        $account['expiryYear'] = '';

        return $account;
    }
}
