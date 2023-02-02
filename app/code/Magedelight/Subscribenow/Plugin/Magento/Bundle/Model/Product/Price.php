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

namespace Magedelight\Subscribenow\Plugin\Magento\Bundle\Model\Product;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Subscription;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;

/**
 * Subscribe now
 * Bundle Product Prices set in on order recurrence time
 * if dynamic price is no
 */
class Price
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Subscription
     */
    private $subscription;

    public function __construct(
        Data $helper,
        Subscription $subscription
    ) {
        $this->subscription = $subscription;
        $this->helper = $helper;
    }

    public function afterGetSelectionFinalTotalPrice(
        $subject,
        $result,
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        if (!$this->helper->isModuleEnable()) {
            return $result;
        }

        if ($bundleProduct->hasSkipValidateTrial() && $bundleProduct->getSkipValidateTrial()) {
            $bundleProduct->setSkipValidateTrial(1);
            $selectionProduct->setSkipValidateTrial(1);
        }

        if ($bundleProduct->hasData('is_subscription_recurring_order')
            && $bundleProduct->getData('is_subscription_recurring_order')
            && $oldBundleOption = $bundleProduct->getData('subscription_bundle_option')
        ) {
            $selectProductOptionId = $selectionProduct->getOptionId();
            if (in_array($selectProductOptionId, array_keys($oldBundleOption))) {
                $optionData = $oldBundleOption[$selectProductOptionId];

                if ($optionData && !empty($optionData['value'])
                    && $optionDataValue = $optionData['value'][0]
                ) {
                    $qty = !empty($optionDataValue['qty']) ? $optionDataValue['qty'] : 0;
                    $price = !empty($optionDataValue['price']) ? $optionDataValue['price'] : 0;

                    if ($qty && $price) {
                        return max(0, $price);
                    }
                }
            }
            $result = 0;
        } elseif (!$bundleProduct->getSkipBundleDiscount()
            && $this->subscription->isSubscriptionProduct($bundleProduct)
            && $bundleProduct->getData('subscription_type') == PurchaseOption::EITHER
        ) {
            return $this->subscription->getFinalPrice($bundleProduct, $result);
        }

        return $result;
    }
}
