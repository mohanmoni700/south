<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin;

use Magedelight\Subscribenow\Model\Subscription;

class PricingPlugin
{

    private $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Set Final Price Product Listing
     *
     * @param \Magento\Catalog\Pricing\Price\FinalPrice $subject
     * @param object $result
     *
     * @return float
     */

    public function afterGetValue(\Magento\Catalog\Pricing\Price\FinalPrice $subject, $result)
    {
        if ($this->subscription->isSubscriptionProduct($subject->getProduct())) {
            $result = $this->subscription->getSubscriptionDiscount($result, $subject->getProduct(), true);
        }
        return $result;
    }
}
