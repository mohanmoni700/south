<?php
namespace Alfakher\StoreCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TogglePaymentMethodsObserver extends \Magento\CustomerBalance\Observer\TogglePaymentMethodsObserver
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->_customerBalanceData->isEnabled()) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $balance = $quote->getCustomerBalanceInstance();
        if (!$balance) {
            return;
        }
        /* start = changes for show payment methods for the partial storecredit */
        if ($quote->getStorecreditType() == 'partial') {
            if ($quote->getUseCustomerBalance()) {
                return;
            }
        }
        /* end = changes for show payment methods for the partial storecredit */
        if ($balance->isFullAmountCovered($quote)) {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', $paymentMethod === 'free');
        }
    }
}
