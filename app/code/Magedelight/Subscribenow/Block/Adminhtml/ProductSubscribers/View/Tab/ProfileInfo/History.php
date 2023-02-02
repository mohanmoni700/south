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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo;

use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo as ParentBlock;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;

class History extends ParentBlock
{

    /**
     * @return string
     */
    public function getSubscriptionStartDate()
    {
        $date = $this->getSubscription()->getSubscriptionStartDate();
        return $this->timezone->date($date)->format('F d, Y');
    }

    public function isAllowUpdateDate()
    {
        $product = $this->getSubscriptionProduct();

        try {
            if ($this->getSubscription()->getParentProductId()) {
                $product = $this->productRepository->getById($this->getSubscription()->getParentProductId());
            }
        } catch (\Exception $ex) {
            $ex->getMessage();
        }

        return $product->getAllowUpdateDate();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canUpdateNextOccurrenceDate()
    {
        if ($this->isEditMode()
            && $this->getSubscriptionProduct()
            && $this->isAllowUpdateDate()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Return next occurrence date
     * @return string
     */
    public function getNextOccurrenceDate($format = 'd F Y')
    {
        $status = $this->getSubscription()->getSubscriptionStatus();
        $date = $this->getSubscription()->getNextOccurrenceDate();

        if (!$date || $date == '0000-00-00 00:00:00'
            || $status == ProfileStatus::COMPLETED_STATUS
            || $status == ProfileStatus::CANCELED_STATUS
            || $status == ProfileStatus::SUSPENDED_STATUS
            || $status == ProfileStatus::FAILED_STATUS
        ) {
            return '-';
        }

        return date($format, strtotime($date));
    }

    public function getJsCurrentDate()
    {
        return date("Y/m/d");
    }

    /**
     * @return bool
     */
    public function hasTrialSubscription()
    {
        return $this->getSubscription()->getTrialBillingAmount() && $this->getSubscription()->getTrialPeriodUnit();
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getTrialMaxCycle()
    {
        $maxCycle = $this->getSubscription()->getTrialPeriodMaxCycle();
        return ($maxCycle)?$maxCycle:__('Repeats until failed or canceled.');
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getBillingMaxCycle()
    {
        $maxCycle = $this->getSubscription()->getPeriodMaxCycles();
        return ($maxCycle)?$maxCycle:__('Repeats until failed or canceled.');
    }

    public function canUpdateBillingFrequency()
    {
        if ($this->isEditMode()) {
            return true;
        }
        return false;
    }

    public function getBillingInterval()
    {
        return $this->helper->getBillingInterval(
            $this->getSubscription()->getBillingFrequencyCycle(),
            $this->getSubscription()
        );
    }
}
