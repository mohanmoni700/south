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

namespace Magedelight\Subscribenow\Block\Cart\Item\Subscription;

use Magedelight\Subscribenow\Block\Cart\Item\Subscription;

class BillingDate extends Subscription
{
    public function isCustomerDefined()
    {
        return (bool) ($this->getProduct()->getDefineStartFrom() == 'defined_by_customer');
    }

    public function getSubscriptionDate()
    {
        return $this->getSubscription()->getSubscriptionStartDate();
    }

    public function getSubscriptionSelectedDate()
    {
        $buyRequest = $this->getBuyRequest();
        
        $subscriptionStartDate = $buyRequest['subscription_start_date'] ?? false;

        if ($subscriptionStartDate) {
            return date('d-m-Y', strtotime($subscriptionStartDate));
        }
        
        return $this->timezone->date()->format('d-m-Y');
    }

    public function getCurrentDate()
    {
        return $this->timezone->date()->format('d-m-Y');
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
     * @return false|string
     * @since 200.7.0
     */
    public function getSubscriptionEndDateSelected()
    {
        $buyRequest = $this->getBuyRequest();

        $subscriptionEndDate = $buyRequest['subscription_end_date'] ?? false;

        if ($subscriptionEndDate) {
            return date('d-m-Y', strtotime($subscriptionEndDate));
        }

        return $this->timezone->date()->format('d-m-Y');
    }

    /**
     * @return bool|null
     * @since 200.7.0
     */
    public function getSubscriptionEndCycle()
    {
        $buyRequest = $this->getBuyRequest();

        $subscriptionEndCycle = $buyRequest['subscription_end_cycle'] ?? false;

        if ($subscriptionEndCycle) {
            return $subscriptionEndCycle;
        }
        return null;
    }

    public function getSubscriptionEndType()
    {
        $buyRequest = $this->getBuyRequest();

        $subscriptionEndType = $buyRequest['end_type'] ?? false;

        if ($subscriptionEndType) {
            return $subscriptionEndType;
        }
        return null;
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
