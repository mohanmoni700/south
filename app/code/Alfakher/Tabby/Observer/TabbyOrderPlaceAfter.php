<?php

declare(strict_types = 1);

namespace Alfakher\Tabby\Observer;

use Alfakher\Tabby\Model\TabbyCheckout;
use Alfakher\Tabby\Model\TabbySession;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class TabbyOrderPlaceAfter implements ObserverInterface
{
    const TABBY_INSTALLMENTS_METHOD_CODE = 'tabby_installments';

    /**
     * @var TabbyCheckout
     */
    private $tabbyCheckout;
    /**
     * @var TabbySession
     */
    private $tabbySession;

    /**
     * @param TabbyCheckout $tabbyCheckout
     * @param TabbySession $tabbySession
     */
    public function __construct(
        TabbyCheckout $tabbyCheckout,
        TabbySession  $tabbySession
    ) {
        $this->tabbyCheckout = $tabbyCheckout;
        $this->tabbySession = $tabbySession;
    }

    /**
     * Observer for sales_order_place_after
     *
     * @param  Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        $paymentMethodCode = $order->getPayment()->getMethod();

        if ($paymentMethodCode == self::TABBY_INSTALLMENTS_METHOD_CODE) {
            $this->tabbyCheckout->setOrder($order);
            $redirectUrl = $this->tabbyCheckout->getOrderRedirectUrl();
            $this->tabbySession->setTabbyRedirectUrl($redirectUrl);
        }
    }
}
