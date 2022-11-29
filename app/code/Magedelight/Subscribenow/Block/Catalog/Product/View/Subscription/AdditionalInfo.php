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

class AdditionalInfo extends Subscription
{

    public function hasAdditionalInfo()
    {
        return count($this->getAdditionalInfo());
    }

    public function getAdditionalInfo()
    {
        /** @since 200.6.5 billing cycle customer side implemented */
        // $this->info['Billing Max Cycle'] = $this->subscriptionService->getBillingMaxCyclesLabel();

        if ((float)$this->getProduct()->getData('discount_amount')) {
            $this->info['Discount Amount'] = $this->getDiscountAmount(true);
        }

        if ($this->getInitialAmount()) {
            $this->info['Initial Fee'] = $this->getInitialAmount(true);
        }

        $this->getTrialInfo();
        return $this->info;
    }

    public function getTrialInfo()
    {
        if (!$this->getSubscription()->getAllowTrial()) {
            return;
        }
        $this->info['Trial Max Cycle'] = $this->subscriptionService->getTrialMaxCycleLabel();

        $this->info['Trial Amount'] = $this->getTrialAmount(true);
    }
}
