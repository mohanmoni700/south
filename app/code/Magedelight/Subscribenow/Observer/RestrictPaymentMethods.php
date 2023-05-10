<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\Event\ObserverInterface;

use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;

/**
 * Payment method event must be in etc/events.xml so
 * whenever file is call it's work fine.
 */
class RestrictPaymentMethods implements ObserverInterface
{

    /**
     * @var SubscribeHelper
     */
    private $subscribeHelper;

    /**
     * RestrictPaymentMethods constructor.
     * @param SubscribeHelper $subscribeHelper
     */
    public function __construct(
        SubscribeHelper $subscribeHelper
    ) {
        $this->subscribeHelper = $subscribeHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->subscribeHelper->isModuleEnable()) {
            return $this;
        }

        $quote = $observer->getData('quote');
        $allowedMethods = $this->subscribeHelper->getAllowedPaymentMethods();
        if (!empty($allowedMethods) && $this->subscribeHelper->hasSubscriptionProduct($quote)) {
            $paymentModel = $observer->getEvent()->getMethodInstance();

            $isWalletApplied = $quote && $quote->hasUsedCheckoutWalletAmout()
                ? $quote->getUsedCheckoutWalletAmout() : 0;

            if ($isWalletApplied > 0) {
                array_push($allowedMethods, 'free');
            }

            if (!in_array($paymentModel->getCode(), $allowedMethods)) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
                return $this;
            }
        }

        return $this;
    }
}
