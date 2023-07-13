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

namespace Magedelight\Subscribenow\Helper;

use Magedelight\Subscribenow\Model\Service\Order\Generate;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Checkout\Model\Session as Quote;
use Magento\Store\Model\ScopeInterface;

class Shipping extends AbstractHelper
{

    /**
     * @var bool (Subscription Item Flag)
     */
    public $hasSubscriptionItem = false;
    /**
     * @var bool (Future Subscription Item Flag)
     */
    public $hasFutureSubscriptionItem = false;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Generate
     */
    private $generate;
    /**
     * @var Quote
     */
    private $checkoutSession;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Context $context,
        Session $session,
        Generate $generate,
        Quote $checkoutSession,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->session = $session;
        $this->generate = $generate;
        $this->checkoutSession = $checkoutSession;
        $this->timezone = $timezone;
    }

    public function isAdmin()
    {
        return $this->session->isLoggedIn();
    }

    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_SUBSCRIBENOW_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return boolean
     */
    public function isSubscriptionWithFreeShipping()
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_SUBSCRIPTION_FREE_SHIPPING,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param \Magento\Shipping\Model\Rate\Result $shippingModel
     *
     * @return bool
     */
    public function isMethodRestricted($shippingModel)
    {
        if ($this->isAutoShipping()) {
            return false;
        }

        $arr = explode('_', $shippingModel->getMethod(), 2);
        $code = $shippingModel->getCarrier() . '_' . $arr[0];

        $restrictedMethod = $this->getAllowedShippingMethods();

        if ($restrictedMethod && !in_array($code, $restrictedMethod)) {
            return true;
        }

        return false;
    }

    public function getAllowedShippingMethods()
    {
        $allowedMethods = $this->scopeConfig->getValue(
            Data::XML_PATH_ALLOWED_SHIPPING_METHODS,
            ScopeInterface::SCOPE_WEBSITE
        );
        $methodList = ($allowedMethods) ? explode(',', $allowedMethods) : [];
        return $methodList;
    }

    /**
     * @return \Magento\Quote\Model\Quote|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentQuote()
    {
        if ($this->isAdmin()) {
            $quote = $this->generate->getCurrentQuote();
        } else {
            $quote = $this->checkoutSession->getQuote();
        }
        return $quote;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasSubscriptionItem()
    {
        $quote = $this->getCurrentQuote();
        if (!$quote) {
            return false;
        }

        foreach ($quote->getAllItems() as $item) {
            $itemRequestData = $item->getBuyRequest()->getData();

            if (!$this->hasSubscriptionItem
                && $this->isSubscription($itemRequestData)) {
                $this->hasSubscriptionItem = true;
            } elseif ($this->isSubscription($itemRequestData)
                && $this->isFutureSubscription($itemRequestData)
                && !$item->hasSubscriptionOrderGenerate()
            ) {
                $this->hasFutureSubscriptionItem = true;
                break;
            }
        }
        return false;
    }

    private function isSubscription($param)
    {
        if (isset($param) &&
            isset($param['options']['_1']) &&
            $param['options']['_1'] == PurchaseOption::SUBSCRIPTION
        ) {
            return true;
        }
        return false;
    }

    private function isFutureSubscription($param)
    {
        if ($param) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string)isset($param['subscription_start_date']) ? $param['subscription_start_date'] : $currentDate;
            $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));
            if ($currentDate !== $requestDate && $subscriptionStartDate > $currentDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * If Auto Select Shipping Method Enable
     * Pls do not validate the shipping restrict plugin on recurring time.
     * @return bool
     */
    public function isAutoShipping()
    {
        $isAutoShipping = $this->scopeConfig->getValue(
            Data::XML_PATH_SELECT_AUTO_SHIPPING,
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($isAutoShipping && $this->generate->getCurrentQuote()) {
            return true;
        }
        return false;
    }
}
