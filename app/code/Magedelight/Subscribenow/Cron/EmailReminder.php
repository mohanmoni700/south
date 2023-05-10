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
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as SubscriptionCollection;
use Magedelight\Subscribenow\Model\Service\EmailService;
use Magedelight\Subscribenow\Model\Service\EmailServiceFactory;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class EmailReminder
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
     * @var CustomerRepositoryInterface
     */
    private $customer;

    /**
     * @var ProductRepositoryInterface
     */
    private $product;

    /**
     * @var EmailServiceFactory
     */
    private $emailService;

    /**
     * @var Json
     */
    private $serialize;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        SubscribeHelper $subscribeHelper,
        SubscriptionCollection $subscriptionCollection,
        TimezoneInterface $timezone,
        CustomerRepositoryInterface $customer,
        ProductRepositoryInterface $product,
        EmailServiceFactory $emailService,
        Json $serialize,
        Logger $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->subscribeHelper = $subscribeHelper;
        $this->subscriptionCollection = $subscriptionCollection;
        $this->timezone = $timezone;
        $this->customer = $customer;
        $this->product = $product;
        $this->emailService = $emailService;
        $this->serialize = $serialize;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    private function getDateTime()
    {
        return $this->timezone->date(null, null, false)->format('Y-m-d H:i:s');
    }

    private function getDaysBeforeReminderOccurrence()
    {
        return (int) max(0, $this->subscribeHelper->getScopeValue('general/reminder_occurrence_before'));
    }

    private function getBeforeDaysDate($today, $time = 'H:i:s')
    {
        $days = $this->getDaysBeforeReminderOccurrence() . ' day';
        return date('Y-m-d ' . $time, strtotime($days, strtotime($today)));
    }

    private function getSubscriptions()
    {
        $today = $this->getDateTime();
        $currentStartDate = $this->getBeforeDaysDate($today, '00:00:00');
        $currentEndDate = $this->getBeforeDaysDate($today, '23:59:59');

        $collection = $this->subscriptionCollection->create()
            ->addFieldToFilter('next_occurrence_date', ['gteq' => $currentStartDate])
            ->addFieldToFilter('next_occurrence_date', ['lteq' => $currentEndDate])
            ->addFieldToFilter('subscription_status', [ProfileStatus::ACTIVE_STATUS,ProfileStatus::PENDING_STATUS]);

        return $collection;
    }

    public function execute()
    {
        $days = $this->getDaysBeforeReminderOccurrence();
        if (!$this->subscribeHelper->isModuleEnable() || !$days) {
            return false;
        }

        $collection = $this->getSubscriptions();
        $this->logger->info("{$collection->getSize()} Reminder email found");
        if ($collection->getSize()) {
            $this->subscriptionProcess($collection, $days);
        }
    }

    private function subscriptionProcess($subscriptions)
    {
        $emailProcceed = 0;

        foreach ($subscriptions as $subscription) {
            $profileId = $subscription->getProfileId();
            $this->logger->info("Reminder email Processing start for subscription profile ID # {$profileId}");

            if (!$this->isValidCustomer($subscription->getCustomerId())) {
                $this->logger->info("not valid customer {$subscription->getCustomerId()} profile ID # {$profileId}");
                continue;
            }

            $this->sendReminderEmail($subscription);
            $this->logger->info("Reminder email Processing End for subscription profile ID # {$profileId}");
            $emailProcceed++;
        }

        $this->logger->info("Total {$emailProcceed} reminder email processed");
    }

    private function isValidCustomer($customerId = 0)
    {
        if ($customerId) {
            $customer = $this->customer->getById($customerId);
            if ($customer->getId()) {
                return true;
            }
        }
        $this->logger->info("Processing stop for subscription customer # {$customerId}. Reason :  this customer does not exist in store.");
        return false;
    }

    public function sendReminderEmail($subscription)
    {
        if (!$this->subscribeHelper->isSubscriptionReminderEmailSend($subscription->getStoreId())) {
            return false;
        }

        $storeId = $subscription->getStoreId();

        $store = $this->storeManager->getStore($storeId);
        $nextDate = date('F d, Y', strtotime($subscription->getNextOccurrenceDate()));
        $product = $this->product->getById($subscription->getProductId(), false, $storeId);

        $vars = [
            'subscription' => $subscription,
            'store_id' => $storeId,
            'nextdate' => $nextDate,
            'productname' => $subscription->getProductName(),
            'sku' => $subscription->getProductSku(),
            'qty' => (float) $subscription->getQtySubscribed(),
            'product_url' => $product->getUrlInStore(),
            'store' => $store
        ];

        try {
            $this->sendEmail($vars, EmailService::EMAIL_REMINDER, $subscription->getSubscriberEmail());
            $this->logger->info("Reminder email sent successfully for # {$subscription->getProfileId()}");
        } catch (\Exception $ex) {
            $this->logger->info("Reminder email not send. Reason : " . $ex->getMessage());
        }
    }

    public function sendEmail($emailVariable, $type, $email)
    {
        $emailService = $this->emailService->create();
        $emailService->setStoreId($emailVariable['store_id']);
        $emailService->setTemplateVars($emailVariable);
        $emailService->setType($type);
        $emailService->setSendTo($email);
        $emailService->send();
    }
}
