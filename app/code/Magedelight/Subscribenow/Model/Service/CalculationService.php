<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Helper\Data as DataHelper;
use Magento\Quote\Model\Quote;

/**
 * Class CalculationService acts as wrapper around actual
 * CalculatorInterface so logic valid for all calculations like
 * min order amount is only done once.
 */
class CalculationService
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var SubscriptionService
     */
    private $subscriptionService;

    /**
     * Base Amount
     */
    private $amount = 0;

    /**
     * CalculationService constructor.
     *
     * @param DataHelper $helper
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(
        DataHelper $helper,
        SubscriptionService $subscriptionService
    ) {
        $this->helper = $helper;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @return $this
     */
    public function calculate(Quote $quote, $type)
    {
        $this->amount = 0;
        if (!$this->helper->isModuleEnable()) {
            return $this->amount;
        }

        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if ($this->subscriptionService->isSubscribed($item) &&
                !$this->isFutureSubscription($type, $item)
            ) {
                $this->amount += $this->getPrice($item, $type);
            }
        }

        return $this;
    }

    /**
     * Return converted amount
     * @return float
     */
    public function getAmount()
    {
        return $this->subscriptionService->getConvertedPrice($this->amount);
    }

    /**
     * Return base amount
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->amount;
    }

    /**
     * @param object $item
     * @return float`
     */
    private function getPrice($item, $type)
    {
        $amount = 0;
        if ($type == 'init_amount') {
            $amount = $this->getProduct($item)->getData('initial_amount');
            if($this->subscriptionService->isFutureItem($item->getBuyRequest()->getData())
                && $amount <= 0){
                $amount = 1;
            }
        } elseif ($this->getProduct($item)->getData('allow_trial') && $type == 'trial_amount') {
            $amount = $item->getQty() * $this->getProduct($item)->getData('trial_amount');
        }

        return $amount;
    }

    private function isFutureSubscription($type, $item)
    {
        if ($type == 'trial_amount') {
            if ($this->subscriptionService->isFutureItem(
                $item->getBuyRequest()->getData()
            ) && !$item->hasSubscriptionOrderGenerate()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $item
     * @return mixed
     */
    private function getProduct($item)
    {
        $product = $item->getProduct();

        $parentId = $this->subscriptionService->getGroupProductIdFromRequest(
            $item->getBuyRequest()->getData()
        );

        if ($parentId) {
            $product = $this->subscriptionService->getProductModel($parentId);

            // Group Product Generate Order time settled init & trial amount
            if ($item->hasSubscriptionOrderGenerate() && $item->getSubscriptionOrderGenerate()) {
                $product->setInitialAmount(0);
            }
        }
        return $product;
    }
}
