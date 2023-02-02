<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category  Magedelight
 * @package   Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin\Magento\Catalog\Model\CustomOptions;

use Magento\Quote\Api\Data\CartItemInterface;
use Magedelight\Subscribenow\Helper\Data;

/**
 * Plugin file for adding product with subscrription otption
 *
 * Class CustomOptionProcessor
 * @package Magedelight\Subscribenow\Plugin\Magento\Catalog\Model\CustomOptions
 */
class CustomOptionProcessor
{
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param $result
     * @param CartItemInterface $cartItem
     * @return mixed
     */
    public function afterConvertToBuyRequest($subject, $result, CartItemInterface $cartItem)
    {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        if ($result && $this->isSubscriptionProduct($result)) {
            $subcriptionOption = $cartItem->getProductOption()->getExtensionAttributes();

            if ($subscriptionDate = $subcriptionOption->getSubscriptionStartDate()) {
                $result->setData('subscription_start_date', $subscriptionDate);
            }
            if ($billingPeriod = $subcriptionOption->getBillingPeriod()) {
                $result->setData('billing_period', $billingPeriod);
            }
        }

        return $result;
    }

    /**
     * @return boolean
     */
    public function isSubscriptionProduct($request = null)
    {
        $result = false;
        if ($request && $requestData = $request->getData()) {
            if (isset($requestData['options'])
                && isset($requestData['options']['_1'])
                && $requestData['options']['_1'] == 'subscription'
            ) {
                $result = true;
            }
        }

        return $result;
    }
}
