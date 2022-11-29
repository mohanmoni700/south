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

namespace Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription;

use Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription;

class BillingPeriod extends Subscription
{
    private $selectedPeriod = null;

    public function getBillingPeriods()
    {
        return $this->subscriptionHelper->getSubscriptionInterval(true, 'interval_label');
    }

    public function isCustomerDefined()
    {
        return $this->getSubscription()->getBillingPeriodType() == 'customer';
    }

    public function getAdminDefinedBillingLabel()
    {
        return __(
            ' %1 %2',
            $this->getSubscription()->getBillingFrequency(),
            ucfirst($this->getSubscription()->getBillingPeriod())
        );
    }

    public function isSelected($period = 0)
    {
        if (!$this->selectedPeriod) {
            $editData = $this->getRequestedParams();
            if ($editData && isset($editData['billing_period'])) {
                $this->selectedPeriod = $editData['billing_period'];
            }
        }

        if ($this->selectedPeriod === $period) {
            return 'selected="selected"';
        }

        return '';
    }

    public function getSubscriptionSelectedBillingPeriod()
    {
        $productEditData = $this->getRequestedParams();
        if ($productEditData && isset($productEditData['billing_period'])) {
            return $productEditData['billing_period'];
        }

        return false;
    }
}
