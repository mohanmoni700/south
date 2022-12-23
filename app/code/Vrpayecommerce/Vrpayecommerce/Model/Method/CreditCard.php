<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class CreditCard extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_creditcard';
    protected $brand = 'VISA MASTER AMEX DINERS JCB';
    protected $methodTitle = 'FRONTEND_PM_CC';

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
            if (!$this->isAdminLoggedIn() && ($this->isRecurring() && $customerSession->isLoggedIn())) {
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
     * get a brand
     * @return string
     */
    public function getBrand()
    {
        $cardSelection = $this->getCardSelection();
        if (isset($cardSelection)) {
            return str_replace(',', ' ', $cardSelection);
        }
        return $this->brand;
    }

    /**
     * get a version data
     * @return array
     */
    public function getVersionData()
    {
        $versionData = parent::getVersionData();

        $versionData['merchant_location'] = $this->getGeneralConfig('merchant_location');

        return $versionData;
    }
}
