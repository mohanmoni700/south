<?php

namespace Corra\Veratad\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Veratad EmailVariables module observer
 */
class SaveEmailVariables implements ObserverInterface
{
    /**
     * Custom Processing Order-Status label
     */
    private const AGE_VERIFICATION_FAILED_LABEL = 'Age Verification Failed';

    /**
     * Order information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport->getOrder();
        $transport['isAgeVerficationFailed'] = false;
        if ($order->getFrontendStatusLabel() == self::AGE_VERIFICATION_FAILED_LABEL) {
            $transport['isAgeVerficationFailed'] = true;
        }
    }
}
