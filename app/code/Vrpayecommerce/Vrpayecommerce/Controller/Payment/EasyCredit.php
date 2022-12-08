<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class EasyCredit extends \Vrpayecommerce\Vrpayecommerce\Controller\Payment
{
    /**
     * set paymentMethod
     * @return void
     */
    protected function setPaymentMethod()
    {
        $this->quote = $this->getQuote();
        $this->paymentMethod = $this->quote->getPayment()->getMethodInstance();
        $this->order = $this->quote;
    }

    /**
     * get order increment id
     * @return string
     */
    protected function getOrderIncrementId()
    {
        $this->quote->reserveOrderId()->save();
        return $this->quote->getReservedOrderId();
    }

    /**
     * get order currency
     * @return string
     */
    protected function getOrderCurrency()
    {
        return $this->quote->getQuoteCurrencyCode();
    }

}
