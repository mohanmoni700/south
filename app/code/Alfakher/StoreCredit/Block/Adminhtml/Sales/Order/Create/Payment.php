<?php

namespace Alfakher\StoreCredit\Block\Adminhtml\Sales\Order\Create;

class Payment extends \Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Create\Payment
{
    /**
     * Method for check partial store credit applied or not
     *
     * @return boolean
     */
    public function getIsPartialStoreCredit()
    {
        if ($this->getUseCustomerBalance()) {
            if ($this->_orderCreate->getQuote()->getStorecreditType() == 'partial') {
                return true;
            }
        }
        return false;
    }

    /**
     * Method for check all store credit applied or not
     *
     * @return boolean
     */
    public function getIsAllStoreCredit()
    {
        if ($this->getUseCustomerBalance()) {
            if ($this->_orderCreate->getQuote()->getStorecreditType() == 'all') {
                return true;
            }
        }
        return false;
    }

    /**
     * Method for get store credit amount
     *
     * @return string
     */
    public function getPartialStoreCreditAmount()
    {
        if ($this->getUseCustomerBalance()) {
            if ($this->_orderCreate->getQuote()->getStorecreditType() == 'partial') {
                if ($this->_orderCreate->getQuote()->getStorecreditPartialAmount()) {
                    return $this->_orderCreate->getQuote()->getStorecreditPartialAmount();
                }
            }
        }
        return '';
    }
}
