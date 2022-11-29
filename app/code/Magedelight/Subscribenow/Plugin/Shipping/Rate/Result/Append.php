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

namespace Magedelight\Subscribenow\Plugin\Shipping\Rate\Result;

use Magedelight\Subscribenow\Helper\Shipping;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;

class Append
{
    /** @var Shipping */
    private $shippingHelper;

    public function __construct(Shipping $shipping)
    {
        $this->shippingHelper = $shipping;
    }

    /**
     * Validate each shipping method before append.
     *
     * @param Result $subject
     * @param AbstractResult|Result $result
     * @return array
     */
    public function beforeAppend($subject, $result)
    {
        if (!$this->shippingHelper->isModuleEnable()) {
            return [$result];
        }

        if (!$result instanceof Method) {
            return [$result];
        }

        // find subscription items
        $this->shippingHelper->hasSubscriptionItem();
        if (!$this->shippingHelper->hasSubscriptionItem) {
            return [$result];
        }

        $isFreeShipping = $this->shippingHelper->isSubscriptionWithFreeShipping();
        if ($isFreeShipping || $this->shippingHelper->hasFutureSubscriptionItem) {
            $result->setIsFreeShipping(true);
        }

        if ($this->shippingHelper->isMethodRestricted($result)) {
            $result->setIsDisabled(true);
        }

        return [$result];
    }
}
