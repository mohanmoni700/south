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

namespace Magedelight\Subscribenow\Cron;

use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as SubscriptionCollection;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Model\Service\OrderService;
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class Generate
{
    /**
     * @var SubscribeHelper
     */
    private $subscribeHelper;
    /**
     * @var SubscriptionCollection
     */
    private $subscriptionCollection;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        SubscribeHelper $subscribeHelper,
        SubscriptionCollection $subscriptionCollection,
        TimezoneInterface $timezone,
        OrderService $orderService,
        Logger $logger
    ) {
    
        $this->subscribeHelper = $subscribeHelper;
        $this->subscriptionCollection = $subscriptionCollection;
        $this->timezone = $timezone;
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->subscribeHelper->isModuleEnable()) {
            return;
        }
        $this->logger->info("generate order cron started...");
        $collection = $this->getSubscriptions();
        
        $this->logger->info("{$collection->getSize()} subscription profile found.");
        if ($collection->getSize()) {
            $_SERVER['HTTP_USER_AGENT'] = '';
            $_SERVER['HTTP_ACCEPT'] = '';
            
            foreach ($collection as $subscription) {
                try {
                    $subscription->load($subscription->getId());
                    $this->orderService->createSubscriptionOrder($subscription, ProductSubscriptionHistory::HISTORY_BY_CRON);
                } catch (\Exception $ex) {
                    $this->logger->info("Error:" . $ex->getMessage());
                    $this->logger->info("Process end with error for subscription profile # " . $subscription->getProfileId());
                }
            }
        }
        
        $this->logger->info("generate order cron finished.");
    }

    private function getSubscriptions()
    {
        $today = $this->timezone->date(null, null, false)->format('Y-m-d 23:59:59');
        $collection = $this->subscriptionCollection->create()
            ->addFieldToFilter('next_occurrence_date', ['lteq' => $today])
            ->addFieldToFilter('subscription_status', ProfileStatus::ACTIVE_STATUS);
        return $collection;
    }
}
