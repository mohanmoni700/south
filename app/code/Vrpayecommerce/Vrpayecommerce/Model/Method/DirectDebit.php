<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class DirectDebit extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_directdebit';
    protected $brand = 'DIRECTDEBIT_SEPA';
    protected $methodTitle = 'FRONTEND_PM_DD';
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
}
