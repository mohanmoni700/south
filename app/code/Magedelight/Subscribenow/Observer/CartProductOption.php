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

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Subscription;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class CartProductOption
 *
 * Group associative child product's additional option set.
 * @package Magedelight\Subscribenow\Observer
 */
class CartProductOption implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Subscription
     */
    private $subscription;
    /**
     * @var Json
     */
    private $serializer;

    /**
     * CartProductOption constructor.
     * @param Data $helper
     * @param Subscription $subscription
     * @param Json $serializer
     */
    public function __construct(
        Data $helper,
        Subscription $subscription,
        Json $serializer
    ) {
        $this->helper = $helper;
        $this->subscription = $subscription;
        $this->serializer = $serializer;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isModuleEnable()) {
            return $this;
        }

        $product = $observer->getData('product');
        $childProduct = $observer->getData('child_product');
        $buyRequest = $observer->getData('buy_request');

        if ($this->getSubscription()->isAdd($product, $buyRequest)) {
            $subscription = $this->getSubscription()->getData($product, $buyRequest)->getSubscriptionData();
            $additionalInfo = $this->getSubscription()->getBuildInfo($subscription, $buyRequest);

            $infoRequest = $childProduct->getCustomOption('info_buyRequest');
            if ($infoRequest && !empty($infoRequest->getValue())) {
                $requestData = $this->serializer->unserialize($infoRequest->getValue());
                $requestData['options'] = ['_1' => 'subscription'];
                $requestData['billing_period'] = $buyRequest->getBillingPeriod();
                $requestData['subscription_start_date'] = $buyRequest->getSubscriptionStartDate();
                if ($buyRequest && !$buyRequest->getSubscriptionStartDate()) {
                    $requestData['subscription_start_date'] = $subscription->getSubscriptionStartDate();
                }
                $requestDataValue = $this->serializer->serialize($requestData);
                $childProduct->addCustomOption('info_buyRequest', $requestDataValue);
            }
            $childProduct->addCustomOption('additional_options', $additionalInfo);
        }

        return $this;
    }

    private function getSubscription()
    {
        return $this->subscription;
    }
}
