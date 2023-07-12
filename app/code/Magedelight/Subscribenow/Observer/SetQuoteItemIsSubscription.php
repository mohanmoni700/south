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
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;

class SetQuoteItemIsSubscription implements ObserverInterface
{
    /**
     * @var SubscribeHelper
     */
    private $subscribeHelper;
    
    /**
     * @param SubscribeHelper $subscribeHelper
     */
    public function __construct(
        SubscribeHelper $subscribeHelper
    ) {
        $this->subscribeHelper = $subscribeHelper;
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->subscribeHelper->isModuleEnable()) {
            return $this;
        }
        
        $quoteItem = $observer->getQuoteItem();
        $buyRequest = $quoteItem->getBuyRequest()->getData();

        $isSubscribed = false;
        $subscriptionOption = $buyRequest['options']['_1'] ?? false;
        if ($subscriptionOption == 'subscription') {
            $isSubscribed = true;
        }

        $quoteItem->setIsSubscription($isSubscribed);
        
        return $this;
    }
}
