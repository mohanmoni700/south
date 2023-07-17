<?php

declare(strict_types=1);

namespace Alfakher\Tabby\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubmitSuccessObserver implements ObserverInterface
{
    const TABBY_INSTALLMENTS_METHOD_CODE = 'tabby_installments';

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        if ($paymentMethod = $quote->getPayment()) {
            $paymentMethodCode = $paymentMethod->getMethod();
            if ($paymentMethodCode == self::TABBY_INSTALLMENTS_METHOD_CODE) {
                $quote->setIsActive(true);
                $quote->setReservedOrderId(null);
            }
        }
    }
}
