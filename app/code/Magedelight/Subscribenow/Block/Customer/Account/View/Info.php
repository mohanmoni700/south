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

namespace Magedelight\Subscribenow\Block\Customer\Account\View;

use Magedelight\Subscribenow\Block\Customer\Account\View;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magedelight\Subscribenow\Model\System\Config\Backend\IntervalType;

class Info extends View
{
    protected $_template = 'customer/account/view/info.phtml';

    /**
     * @return string
     */
    public function getSubscriptionStartDate()
    {
        $date = $this->getSubscription()->getSubscriptionStartDate();
        return $this->formatDate($date, 1);
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
            && $this->isAllowUpdateDate()) {
            return true;
        }
        return false;
    }

    /**
     * Return next occurrence date
     * @return string
     */
    public function getNextOccurrenceDate()
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

        return $this->formatDate($date, 1);
    }

    public function getJsCurrentDate()
    {
        return $this->timezone->date()->format('Y/m/d');
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
        return ($maxCycle) ? $maxCycle : __('Repeats until failed or canceled.');
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getBillingMaxCycle()
    {
        $maxCycle = $this->getSubscription()->getPeriodMaxCycles();
        return ($maxCycle) ? $maxCycle : __('Repeats until failed or canceled.');
    }

    public function canUpdateBillingFrequency()
    {
        $isUpdate = $this->getSubscription()->getIsUpdateBillingFrequency();
        if ($this->isEditMode() && $isUpdate && $this->subscribeHelper->canUpdateBillingFrequency()) {
            return true;
        }
        return false;
    }

    public function getBillingInterval()
    {
        return $this->subscribeHelper->getBillingInterval(
            $this->getSubscription()->getBillingFrequencyCycle(),
            $this->getSubscription()
        );
    }
}
