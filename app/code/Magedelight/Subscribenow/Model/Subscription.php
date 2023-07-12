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

namespace Magedelight\Subscribenow\Model;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magedelight\Subscribenow\Model\Service\DiscountService;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Bundle\Model\Product\TypeFactory as BundleTypeFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Subscription
 */
class Subscription
{
    /**
     * Custom Extension Attribute Columns
     */
    const INIT_AMOUNT_FIELD_NAME = 'subscribenow_init_amount';
    const TRIAL_AMOUNT_FIELD_NAME = 'subscribenow_trial_amount';

    /* @since 200.7.0 */
    const END_TYPE_CYCLE = 'md_end_cycle';
    const END_TYPE_INFINITE = 'md_end_infinite';
    const END_TYPE_DATE = 'md_end_date';

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var Json
     */
    public $serialize;

    /**
     * @var SubscriptionService
     */
    public $service;

    /**
     * @var Http
     */
    public $request;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var BundleTypeFactory
     */
    private $bundleTypeFactory;

    /**
     * @var DiscountService
     */
    private $discountService;

    private $childProduct = null;
    private $hasParent = false;
    private $parentProduct = null;
    private $skipProductTrialValidation = false;
    private $bundleParentId = null;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Subscription constructor
     *
     * @param Data $helper
     * @param Json $serialize
     * @param SubscriptionService $service
     * @param Http $request
     * @param CartRepositoryInterface $quoteRepository
     * @param PriceHelper $priceHelper
     * @param BundleTypeFactory $bundleTypeFactory
     * @param DiscountService $discountService
     * @param Registry $registry
     * @param DateTime $dateTime
     */
    public function __construct(
        Data $helper,
        Json $serialize,
        SubscriptionService $service,
        Http $request,
        CartRepositoryInterface $quoteRepository,
        PriceHelper $priceHelper,
        BundleTypeFactory $bundleTypeFactory,
        DiscountService $discountService,
        Registry $registry,
        DateTime $dateTime
    ) {
        $this->helper = $helper;
        $this->serialize = $serialize;
        $this->service = $service;
        $this->request = $request;
        $this->quoteRepository = $quoteRepository;
        $this->priceHelper = $priceHelper;
        $this->bundleTypeFactory = $bundleTypeFactory;
        $this->discountService = $discountService;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
    }

    /**
     * Get Discounted Final Price
     *
     * @param Object $product
     * @param float $finalPrice
     *
     * @return float Price
     */
    public function getFinalPrice($product, $finalPrice)
    {
        return $this->getSubscriptionDiscount($finalPrice, $product);
    }

    /**
     * @param float $finalPrice
     * @param object $product
     * @param bool $convert
     * @return float Price
     */
    public function getSubscriptionDiscount($finalPrice, $product, $convert = false)
    {
        if (!$this->helper->isModuleEnable()) {
            return $finalPrice;
        }

        $this->hasParent = false;
        $this->parentProduct = null;

        if ($product->getTypeId() == 'bundle' &&
            $product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
            $this->skipProductTrialValidation = true;
        }

        $optionPrice = $this->getOptionPrice($product);
        $price = $finalPrice;

        if ($this->isSubscriptionProduct($product)) {
            $price = $finalPrice - $optionPrice;
            $type = $this->getDiscountType($product);
            $discount = ($convert) ? $this->service->getConvertedPrice($this->getDiscountAmount($product)) : $this->getDiscountAmount($product);

            $price = $this->discountService->calculateDiscountByValue($price, $type, $discount);
            $price += $optionPrice;

            if ($product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
                return max(0, $price);
            }

            if ($this->getService()->isFutureSubscription($product) ||
                ($product->getAllowTrial() && $product->getTrialAmount() > 0)) {
                $product->setCustomPrice(0);
            }

            /** set custom price to product for trial & future items
             * && show custom price everywhere excluding product detail page */
            if ($product->hasCustomPrice() && $this->request->getRouteName() != 'catalog') {
                $price = $product->getCustomPrice(0);
            }
        }

        if ($product->hasSkipDiscount() && $product->getSkipDiscount()) {
            return $price - $optionPrice;
        }

        return max(0, $price);
    }

    private function getOptionPrice($product)
    {
        $finalPrice = 0;
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            $basePrice = $finalPrice;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                }
            }
        }

        return $finalPrice;
    }

    private function getDiscountType($product)
    {
        if ($this->hasParent) {
            return $this->parentProduct->getDiscountType();
        }
        return $product->getDiscountType();
    }

    private function getDiscountAmount($product)
    {
        if ($this->hasParent) {
            return $this->parentProduct->getDiscountAmount();
        }
        return $product->getDiscountAmount();
    }

    /**
     * @return SubscriptionService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param $product
     * @return mixed|null
     */
    private function getBundleParentId($product)
    {
        if ($params = $this->request->getParams()) {
            if (isset($params['super_group'])) {
                return false;
            }
        }

        if ($product->getTypeId() == 'bundle') {
            $this->bundleParentId = $product->getId();
        }

        $ids = [];

        if ($product->hasCustomOption('bundle_identity') &&
            $bundleIdentity = $product->getCustomOption('bundle_identity')->getValue()
        ) {
            $ids = explode('_', $bundleIdentity, 2);
        }

        if (!$ids) {
            $ids = $this->bundleTypeFactory->create()->getParentIdsByChild($product->getId());
        }

        $bundleKey = 0;
        if ($ids) {
            $idsFlip = array_flip($ids);
            if (isset($idsFlip[$this->bundleParentId])) {
                $bundleKey = $idsFlip[$this->bundleParentId];
            }
        }

        return ($ids && isset($ids[$bundleKey])) ? $ids[$bundleKey] : null;
    }


    /**
     * If Valid Data
     * Show Subscription Price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isSubscriptionProduct($product)
    {
        $parentId = $this->getBundleParentId($product);
        if (!$parentId) {
            $parentId = $this->getService()->getGroupedParentId($product);
        }


        if ($product && $parentId) {
            $this->childProduct = $product;

            /** @var \Magento\Catalog\Model\Product $parentProduct */
            $parentProduct = $this->getService()->getProductModel($parentId);

            if ($this->skipProductTrialValidation) {
                $product->setSkipValidateTrial(1);
            }

            if ($this->isSubscriptionProduct($parentProduct)) {
                /** for bundle option cart price value
                 * see bundle price plugin
                 */
                if ($parentProduct->getTypeId() == 'bundle'
                    && $parentProduct->getData('subscription_type') == 'either'
                ) {
                    return false;
                }
                $this->hasParent = true;
                $this->parentProduct = $parentProduct;

                if ($this->getService()->isFutureSubscription($parentProduct)
                    || ($parentProduct->getAllowTrial() && $parentProduct->getTrialAmount() > 0
                        && $this->isProfileInTrial())
                ) {
                    $product->setCustomPrice(0); // Set child product price to zero
                }
                return true;
            }
        }

        if ($product->hasSkipDiscount() && $product->getSkipDiscount()) {
            return false;
        }

        if ($product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
            return true;
        }

        $isSubscription = $product->getIsSubscription();
        $subscriptionType = $product->getSubscriptionType();
        if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
            return true;
        } elseif ($this->isProductWithSubscriptionOption($product)) {
            return true;
        }

        return false;
    }

    private function getCurrentProfile()
    {
        $profile = $this->registry->registry('current_profile');
        if (!$profile) {
            $profile = $this->registry->registry('md_subscribenow_product_subscriber');
        }
        return $profile;
    }

    private function isProfileInTrial()
    {
        $profile = $this->getCurrentProfile();
        if (!$profile) {
            return true;
        }

        if ($profile->getIsTrial() && $profile->isTrialPeriod()) {
            return true;
        }
        return false;
    }

    /**
     * Check Current Product have Subscription Option
     *
     * @param $product
     * @return boolean
     */
    private function isProductWithSubscriptionOption($product)
    {
        $infoRequest = $product->getCustomOption('info_buyRequest');
        if ((!$infoRequest || !$infoRequest->getValue()) && $this->childProduct) {
            $infoRequest = $this->childProduct->getCustomOption('info_buyRequest');
        }

        if ($infoRequest && $infoRequest->getValue()) {
            $requestData = $this->serialize->unserialize($infoRequest->getValue());
            if ($this->getService()->checkProductRequest($requestData)) {
                return true;
            }
        }
        return false;
    }

    private function getOptionPriceHtml($amount)
    {
        if ($amount) {
            return strip_tags($amount);
        }
    }

    /**
     * Build Cart Summary
     *
     * @param $subscription
     * @param $request
     * @return bool|false|string
     */
    public function getBuildInfo($subscription, $request)
    {
        $info[] = [
            'code' => 'billing_period_title',
            'label' => $this->helper->getBillingPeriodTitle(),
            'value' => $this->getBillingPeriod($subscription, $request)
        ];

        $info[] = [
            'code' => 'billing_cycle_title',
            'label' => $this->helper->getBillingCycleTitle(),
            'value' => $this->getBillingCycle($subscription, $request)
        ];

        if ($subscription->getInitialAmount() > 0) {
            $info[] = [
                'code' => 'init_amount',
                'label' => $this->helper->getInitAmountTitle(),
                'value' => $this->getOptionPriceHtml($this->getInitialAmount($subscription)),
                'has_html' => true,
            ];
        }

        if ($subscription->getAllowTrial() && !$this->getService()->isFutureItem($request)) {
            $info[] = [
                'code' => 'trial_amount',
                'label' => $this->helper->getTrialAmountTitle(),
                'value' => $this->getOptionPriceHtml($this->getTrialAmount($subscription)),
                'has_html' => true,
            ];

            $info[] = [
                'code' => 'trial_period_title',
                'label' => $this->helper->getTrialPeriodTitle(),
                'value' => $this->getTrialPeriod($subscription)
            ];

            $info[] = [
                'code' => 'trial_cycle_title',
                'label' => $this->helper->getTrialCycleTitle(),
                'value' => $this->getTrialCycle($subscription)
            ];
        }

        $info[] = [
            'code' => 'md_sub_start_date',
            'label' => $this->helper->getSubscriptionStartDateTitle(),
            'value' => $this->getSubscriptionStartDate($subscription, $request),
        ];

        /* Added @since 200.7.0 */
        if($this->getSubscriptionEndDate($subscription, $request)) {
            $info[] = [
                'code' => 'md_sub_end_date',
                'label' => $this->helper->getSubscriptionEndDateTitle(),
                'value' => $this->getSubscriptionEndDate($subscription, $request),
            ];
        }

        return $this->serialize->serialize($info);
    }

    /**
     * Get Initial Amount with formatted price
     * @param $subscription
     * @return mixed
     */
    private function getInitialAmount($subscription)
    {
        return $this->priceHelper->currency($subscription->getInitialAmount(), true);
    }

    /**
     * Get Trial Amount with formatted price
     * @param $subscription
     * @return mixed
     */
    private function getTrialAmount($subscription)
    {
        if ($subscription->getTrialAmount()) {
            return $this->priceHelper->currency($subscription->getTrialAmount(), true);
        }
        return $this->priceHelper->currency(0.00);
    }

    /**
     * Trial Period
     *
     * @param object $subscription
     * @return string
     */
    private function getTrialPeriod($subscription)
    {
        return $subscription->getTrialPeriodLabel();
    }

    /**
     * Trial Period Cycle
     *
     * @param object $subscription
     * @return string
     */
    private function getTrialCycle($subscription)
    {
        return ($subscription->getTrialMaxCycle()) ? __('%1 time(s)', $subscription->getTrialMaxCycle()) : __('Repeats until failed or canceled');
    }

    /**
     * Subscription Start Date
     *
     * @param object $subscription
     * @return string
     */
    private function getSubscriptionStartDate($subscription, $request = null)
    {
        if ($subscription->getDefineStartFrom() == "defined_by_customer") {
            if ($request) {
                return $request->getData('subscription_start_date');
            }
            return $this->request->getPostValue('subscription_start_date');
        }
        return $this->getService()->getSubscriptionStartDate();
    }

    /**
     * Subscription End Date
     *
     * @param object $subscription
     * @return string
     * @since 200.7.0
     */
    private function getSubscriptionEndDate($subscription, $request = null)
    {
        if ($request->getData('end_type') == "md_end_date") {
            return $request->getData('subscription_end_date');
        }

        return false;
    }

    /**
     * Get Billing Period
     *
     * @param object $subscription
     * @return string
     */
    public function getBillingPeriod($subscription, $request = null)
    {
        if ($subscription->getBillingPeriodType() == 'customer') {
            if ($request) {
                $billingFrequency = $this->helper->getIntervalLabel($request->getData('billing_period'));
            } else {
                $billingFrequency = $this->helper->getIntervalLabel($this->request->getPostValue('billing_period'));
            }
        } else {
            $billingFrequency = $subscription->getBillingFrequencyLabel();
        }

        return ucfirst($billingFrequency);
    }

    /**
     * Get Billing Cycle
     *
     * @param $subscription
     * @param $request
     * @return \Magento\Framework\Phrase
     */
    private function getBillingCycle($subscription, $request)
    {
        if($this->service->getAllowEndDate() && isset($request['end_type'])) {
            $finalCycle = $this->service->endCycleCalculation($request);
            return ($finalCycle) ?
                __('Repeat %1 time(s)', $finalCycle) :
                __('Repeats until failed or canceled');
        } else {
            return ($subscription->getBillingMaxCycles()) ?
                __('Repeat %1 time(s)', $subscription->getBillingMaxCycles()) :
                __("Repeats until failed or canceled");
        }
    }

    /**
     * Get Subscription Object
     *
     * @param object $product
     * @return object
     */
    public function getData($product, $request = null)
    {
        return $this->getService()->getProductSubscriptionDetails($product, $request);
    }

    /**
     * Check available product is valid to
     * add as subscription product
     *
     * @param $product
     * @return boolean
     */
    public function isAdd($product, $request = null)
    {
        if ($request) {
            $params = $request->getData();
        } else {
            $params = $this->request->getParams();
        }

        $isSubscription = $product->getIsSubscription();
        $subscriptionType = $product->getSubscriptionType();

        if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
            return true;
        } elseif ($this->getService()->checkProductRequest($params)) {
            return true;
        } elseif ($this->isProductWithSubscriptionOption($product)) {
            return true;
        }

        return false;
    }

    /**
     * Check if product is valid to buy from listing
     * and return product page url if not valid.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isValidBuyFromList($product)
    {
        $isSubscriptionProduct = $product->getIsSubscription();
        $productSubscriptionType = $product->getSubscriptionType();
        $defineStartFrom = $product->getDefineStartFrom();
        $billingPeriodDefineBy = $product->getBillingPeriodType();

        if ($this->request->getFullActionName() != 'catalog_product_view'
            && $isSubscriptionProduct == '1'
            && ($productSubscriptionType == PurchaseOption::EITHER
                || $defineStartFrom == "defined_by_customer"
                || $billingPeriodDefineBy == "customer")
        ) {
            return false;
        }

        return true;
    }
}
