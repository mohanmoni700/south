<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription;

use Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magedelight\Subscribenow\Model\Source\SubscriptionStart;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;

class BillingDate extends Subscription
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * BillingDate constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscriptionHelper $subscriptionHelper
     * @param TimezoneInterface $timezone
     * @param SubscriptionService $subscriptionService
     * @param priceHelper $priceHelper
     * @param Json $serialize
     * @param Session $customerSession
     * @param LocaleFormat $localeFormat
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscriptionHelper $subscriptionHelper,
        TimezoneInterface $timezone,
        SubscriptionService $subscriptionService,
        PriceHelper $priceHelper,
        Json $serialize,
        Session $customerSession,
        LocaleFormat $localeFormat,
        DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $registry, $subscriptionHelper, $subscriptionService, $priceHelper, $serialize, $customerSession, $localeFormat, $data);
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    /**
     * @return bool
     */
    public function isCustomerDefined()
    {
        return $this->getSubscription()->getDefineStartFrom() == SubscriptionStart::DEFINE_BY_CUSTOMER;
    }

    /**
     * @return bool
     * @since 200.7.0
     */
    public function isEndDateAllowed()
    {
        return $this->getSubscription()->getAllowSubscriptionEndDate();
    }

    /**
     * @return mixed
     */
    public function getSubscriptionDate()
    {
        return $this->getSubscription()->getSubscriptionStartDate();
    }

    /**
     * @return string
     */
    public function getCurrentDate()
    {
        return $this->timezone->date()->format('d-m-Y');
    }

    /**
     * @return false|string
     */
    public function getSubscriptionSelectedDate()
    {
        $productEditData = $this->getRequestedParams();

        if ($productEditData && isset($productEditData['subscription_start_date'])) {
            return date('d-m-Y', strtotime($productEditData['subscription_start_date']));
        }

        return $this->timezone->date()->format('d-m-Y');
    }

    /**
     * @return false|string
     * @since 200.7.0
     */
    public function getSubscriptionEndDateSelected()
    {
        $date = $this->timezone->date();
        $productEditData = $this->getRequestedParams();

        if ($productEditData && isset($productEditData['subscription_end_date'])) {
            return date('d-m-Y', strtotime($productEditData['subscription_end_date']));
        }
        if ($this->getSubscription()->getDefineStartFrom() == SubscriptionStart::LAST_DAY_MONTH) {
            return $date->modify('first day of next month')->format('d-m-Y');
        }

        if ($this->getSubscription()->getDefineStartFrom() == SubscriptionStart::EXACT_DAY) {
            $day = ($this->subscriptionService->getDayOfMonth()) ? $this->subscriptionService->getDayOfMonth() : 'd';
            $currentDate = $date->format('d-m-Y');
            $dayOfMonthDateCheck = $date->format($day.'-m-Y');
            $day = sprintf("%02d", $day + 1);
            $dayOfMonthDate = $date->format($day.'-m-Y');

            return ($dayOfMonthDateCheck >= $currentDate)
                ? $dayOfMonthDate
                : $date->modify('+1 month')->format($day.'-m-Y');
        }
        $afterOneDay = "+1 Days";
        $timeStamp = $this->dateTime->timestamp($afterOneDay);

        return $this->dateTime->gmtDate('d-m-Y', $timeStamp);
    }

    /**
     * @return bool|null
     * @since 200.7.0
     */
    public function getSubscriptionEndCycle()
    {
        $productEditData = $this->getRequestedParams();

        $subscriptionEndCycle = $productEditData['subscription_end_cycle'] ?? false;

        if ($subscriptionEndCycle) {
            return $subscriptionEndCycle;
        }
        return null;
    }

    public function getSubscriptionEndType()
    {
        $productEditData = $this->getRequestedParams();
        $subscriptionEndType = $productEditData['end_type'] ?? false;

        if ($subscriptionEndType) {
            return $subscriptionEndType;
        }

        return \Magedelight\Subscribenow\Model\Subscription::END_TYPE_INFINITE;
    }

    /**
     * @param $endType
     * @return string
     */
    public function isCheckedType($endType)
    {
        $subscriptionEndType = $this->getSubscriptionEndType();
        if ($endType == $subscriptionEndType) {
            return 'checked="checked"';
        }
        return '';
    }
}
