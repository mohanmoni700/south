<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Subscription;

/**
 * Class QuoteSubmitBefore
 *
 * set trial & initial fees into order object
 * so whenever online payment captured it will
 * send extra fees as well
 */
class QuoteSubmitBefore implements ObserverInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * Constructor
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $isModuleEnabled = $this->helper->isModuleEnable();
        if (!$isModuleEnabled) {
            return $this;
        }

        $quote = $observer->getQuote();

        if ($this->helper->hasSubscriptionProduct($quote)) {
            $order = $observer->getOrder();
            $initAmountFee = $quote->getSubscribenowInitAmount();
            $initAmountBaseFee = $quote->getBaseSubscribenowInitAmount();
            $trialAmountFee = $quote->getSubscribenowTrialAmount();
            $trialAmountBaseFee = $quote->getBaseSubscribenowTrialAmount();

            if ($initAmountFee && $initAmountBaseFee) {
                $order->setData(Subscription::INIT_AMOUNT_FIELD_NAME, $initAmountFee);
                $order->setData('base_' . Subscription::INIT_AMOUNT_FIELD_NAME, $initAmountBaseFee);
            }

            if ($trialAmountFee && $trialAmountBaseFee) {
                $order->setData(Subscription::TRIAL_AMOUNT_FIELD_NAME, $trialAmountFee);
                $order->setData('base_' . Subscription::TRIAL_AMOUNT_FIELD_NAME, $trialAmountBaseFee);
            }

            $SubscriptionParentId = $quote->getSubscriptionParentId();
            if ($SubscriptionParentId) {
                $order->setSubscriptionParentId($SubscriptionParentId);
            }
        }


        return $this;
    }
}
