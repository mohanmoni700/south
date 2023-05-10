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

namespace Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Source\BillingPeriodBy;
use Magedelight\Subscribenow\Model\Source\DiscountType;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magedelight\Subscribenow\Model\Source\SubscriptionStart;
use Magedelight\Subscribenow\Model\Subscription;
use Magento\Catalog\Model\ProductFactory as ProductModelFactory;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;

class SubscriptionService
{

    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var DataObjectFactory
     */
    private $dataObject;

    /**
     * @var DataObjectFactory
     */
    private $subscriptionObject;

    /**
     * @var Json
     */
    private $serialize;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var CartRepositoryInterfaceFactory
     */
    protected $cartRepositoryFactory;

    /**
     * @var OrderServiceFactory
     */
    protected $orderServiceFactory;
    /**
     * @var ProductModelFactory
     */
    private $productModelFactory;

    /**
     * @var ProductModelFactory
     */
    private $productModel = null;

    private $currentProductObject = null;

    /**
     * SubscriptionService constructor.
     * @param SubscriptionHelper $subscriptionHelper
     * @param DataObjectFactory $dataObject
     * @param Json $serializer
     * @param TimezoneInterface $timezone
     * @param PriceCurrency $priceCurrency
     * @param CartRepositoryInterfaceFactory $cartRepositoryFactory
     * @param OrderServiceFactory $orderServiceFactory
     */
    public function __construct(
        SubscriptionHelper $subscriptionHelper,
        DataObjectFactory $dataObject,
        Json $serializer,
        TimezoneInterface $timezone,
        PriceCurrency $priceCurrency,
        CartRepositoryInterfaceFactory $cartRepositoryFactory,
        OrderServiceFactory $orderServiceFactory,
        ProductModelFactory $productModelFactory
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->dataObject = $dataObject;
        $this->serialize = $serializer;
        $this->timezone = $timezone;
        $this->priceCurrency = $priceCurrency;
        $this->cartRepositoryFactory = $cartRepositoryFactory;
        $this->orderServiceFactory = $orderServiceFactory;
        $this->productModelFactory = $productModelFactory;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Create Subscription Object
     * @return $this
     */
    protected function intSubscriptionObject()
    {
        $this->subscriptionObject = $this->dataObject->create();
        return $this;
    }

    /**
     * Set Subscription Data Object
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    protected function setSubscriptionData($key, $value)
    {
        $this->subscriptionObject->setData($key, $value);
    }

    /**
     * Get Subscription Data
     * @return object
     */
    public function getSubscriptionData()
    {
        return $this->subscriptionObject;
    }

    /**
     * Is Subscription Product
     * @return int
     */
    public function getIsSubscription()
    {
        return $this->getProduct()->getIsSubscription();
    }

    /**
     * Subscription Type
     * subscription|either
     *
     * @return string
     */
    public function getSubscriptionType()
    {
        return $this->getProduct()->getSubscriptionType();
    }

    /**
     * Discount Type
     * fixed|percentage
     *
     * @return string
     */
    public function getDiscountType()
    {
        return $this->getProduct()->getDiscountType();
    }

    /**
     * Discount Amount
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getProduct()->getDiscountAmount();
    }

    /**
     * Get Initial Amount
     * @return float
     */
    public function getInitialAmount()
    {
        return $this->getProduct()->getInitialAmount();
    }

    /**
     * Get Subscription Start From
     * @return mixed
     */
    public function getDefineStartFrom()
    {
        return $this->getProduct()->getDefineStartFrom();
    }

    /**
     * Get Day of month
     * @return mixed
     */
    public function getDayOfMonth()
    {
        return sprintf("%02d", $this->getProduct()->getDayOfMonth());
    }

    /**
     * Get Billing Period Type
     * @return mixed
     */
    public function getBillingPeriodType()
    {
        return $this->getProduct()->getBillingPeriodType();
    }

    /**
     * Get Billing Period Time
     * @return mixed
     */
    public function getBillingPeriod()
    {
        return $this->getProduct()->getBillingPeriod();
    }

    /**
     * Get Trial Period Time
     * @return mixed
     */
    public function getTrialPeriod()
    {
        return $this->getProduct()->getTrialPeriod();
    }

    /**
     * Allow Update Date
     * @return boolean
     */
    public function getAllowUpdateDate()
    {
        return $this->getProduct()->getAllowUpdateDate();
    }

    /**
     * Get Billing Maximum Cycles
     * @return mixed
     */
    public function getBillingMaxCycles()
    {
        return $this->getProduct()->getBillingMaxCycles();
    }

    /**
     * Is Allow Trial
     * @return int
     */
    public function getAllowTrial()
    {
        if ($this->getProduct()->getAllowTrial() && $this->getTrialAmount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get Trial Max Cycle
     * @return int
     */
    public function getTrialMaxcycle()
    {
        return $this->getProduct()->getTrialMaxcycle();
    }

    /**
     * Get Trial Amount
     * @return float
     */
    public function getTrialAmount()
    {
        return $this->getProduct()->getTrialAmount();
    }

    /**
     * Get Subscription Start Date
     * @return mixed
     */
    public function getSubscriptionStartDate()
    {
        $date = $this->timezone->date();

        if ($this->getDefineStartFrom() == SubscriptionStart::MOMENT) {
            return $date->format('d-m-Y');
        }

        if ($this->getDefineStartFrom() == SubscriptionStart::LAST_DAY_MONTH) {
            return $date->format('t-m-Y');
        }

        if ($this->getDefineStartFrom() == SubscriptionStart::EXACT_DAY) {
            $day = ($this->getDayOfMonth()) ? $this->getDayOfMonth() : 'd';

            $currentDate = $date->format('d-m-Y');
            $dayOfMonthDate = $date->format($day. '-m-Y');

            return ($dayOfMonthDate >= $currentDate)
                ? $dayOfMonthDate
                : $date->modify('+1 month')->format($day. '-m-Y');
        }
    }

    /**
     * Get Subscription Interval
     * @return null|array
     */
    public function getSubscriptionInterval($key = null)
    {
        $interval = $this->subscriptionHelper->getSubscriptionInterval();

        if (!empty($interval) && array_key_exists($key, $interval)) {
            return $interval[$key];
        } elseif (!empty($interval)) {
            return reset($interval);
        }
    }

    /**
     * Set Product Final Price
     * @param float $price
     * @return \Magento\Catalog\Model\Product
     */
    public function setProductFinalPrice($price)
    {
        return $this->getProduct()->setData('finalPrice', $price);
    }

    /**
     * Get Product Final Price
     * @return float
     */
    public function getProductFinalPrice()
    {
        return $this->getProduct()->getData('final_price');
    }

    /**
     * Set Subscription Interval Data
     * @return void
     */
    protected function setSubscriptionIntervalData($request)
    {
        $billingPeriod = $this->getBillingPeriod();
        $subscriptionInterval = $this->getSubscriptionInterval($billingPeriod);

        $this->setSubscriptionData('billing_period_interval', $billingPeriod);
        $this->setSubscriptionData('billing_period', $subscriptionInterval['interval_type']);
        $this->setSubscriptionData('billing_frequency', $subscriptionInterval['no_of_interval']);
        $this->setSubscriptionData('billing_frequency_label', $subscriptionInterval['interval_label']);

        /* @since 200.7.0 version */
        if($this->getAllowEndDate() && isset($request['end_type'])) {
            $periodMaxCycle = $this->endCycleCalculation($request);
            $this->setSubscriptionData('billing_max_cycles', $periodMaxCycle);
        }
    }

    /**
     * Set Trial Period Data
     * @return void
     */
    protected function setTrialData()
    {
        $this->setSubscriptionData('allow_trial', $this->getAllowTrial());
        if ($this->getAllowTrial()) {
            $this->setSubscriptionData('trial_max_cycle', $this->getTrialMaxcycle());
            $this->setSubscriptionData('trial_amount', $this->getTrialAmount());

            $trialPeriod = $this->getTrialPeriod();
            $trialInterval = $this->getSubscriptionInterval($trialPeriod);

            $this->setSubscriptionData('trial_period', $trialInterval['interval_type']);
            $this->setSubscriptionData('trial_frequency', $trialInterval['no_of_interval']);
            $this->setSubscriptionData('trial_period_label', $trialInterval['interval_label']);
        }
    }

    /**
     * Allow Update Date
     * @return boolean
     * @since 200.7.0
     */
    public function getAllowEndDate()
    {
        return $this->getProduct()->getAllowSubscriptionEndDate();
    }

    /**
     * @param $product
     * @param array|null $request
     * @return $this
     */
    public function getProductSubscriptionDetails($product, $request = null)
    {
        $this->setProduct($product)->intSubscriptionObject();

        $this->setSubscriptionData('is_subscription', $this->getIsSubscription());
        $this->setSubscriptionData('subscription_type', $this->getSubscriptionType());
        $this->setSubscriptionData('initial_amount', $this->getInitialAmount());
        $this->setSubscriptionData('define_start_from', $this->getDefineStartFrom());
        $this->setSubscriptionData('subscription_start_date', $this->getSubscriptionStartDate());
        $this->setSubscriptionData('allow_subscription_end_date', $this->getAllowEndDate());
        $this->setSubscriptionData('billing_period_type', $this->getBillingPeriodType());
        $this->setSubscriptionData('billing_max_cycles', $this->getBillingMaxCycles());
        $this->setSubscriptionData('allow_update_date', $this->getAllowUpdateDate());

        $this->setSubscriptionIntervalData($request);
        $this->setTrialData();

        $this->getSubscriptionDiscountAmount();
        $this->buildRequest($request);

        return $this;
    }

    /**
     * Subscription Amount
     * @return int|float
     */
    public function getSubscriptionDiscountAmount($amount = null)
    {
        $baseDiscountAmount = 0;
        $discountAmount = 0;
        $productPrice = ($amount) ?: $this->getProductFinalPrice();

        if ($this->getDiscountAmount()) {
            $baseDiscountAmount = $this->getDiscountAmount();

            if ($this->getDiscountType() == DiscountType::PERCENTAGE) {
                $baseDiscountAmount = $productPrice * ($this->getDiscountAmount() / 100);
            }

            $discountAmount = $this->getConvertedPrice($baseDiscountAmount);
        }

        $this->setSubscriptionData('discount_type', $this->getDiscountType());
        $this->setSubscriptionData('base_discount_amount', $baseDiscountAmount);
        $this->setSubscriptionData('discount_amount', $discountAmount);

        return $baseDiscountAmount;
    }

    /**
     * Overwrite existing billing data
     *
     * @param array $request
     * @return void
     */
    protected function setQuoteBillingPeriod($request = [])
    {
        if ($request && isset($request['billing_period']) && $request['billing_period']) {
            $subscriptionInterval = $this->getSubscriptionInterval($request['billing_period']);

            $this->setSubscriptionData('billing_period', $subscriptionInterval['interval_type']);
            $this->setSubscriptionData('billing_frequency', $subscriptionInterval['no_of_interval']);
            $this->setSubscriptionData('billing_frequency_label', $subscriptionInterval['interval_label']);

            /* @since 200.7.0 version */
            if($this->getAllowEndDate() && isset($request['end_type'])){
                $periodMaxCycle = $this->endCycleCalculation($request);
                $this->setSubscriptionData('billing_max_cycles', $periodMaxCycle);
            }
        }
    }

    /**
     * Set Requested Payload
     * @param $request
     */
    protected function buildRequest($request)
    {
        $requestPayload = [];
        $requestPayload['is_subscription'] = $this->checkProductRequest($request) ? 1 : 0;

        if (isset($requestPayload['is_subscription']) && $requestPayload['is_subscription'] == 1) {
            $this->setQuoteBillingPeriod($request);
        }

        $this->setSubscriptionData('request_payload', $requestPayload);
    }

    /**
     * Get Converted Price with multi-currency
     * @param mixed $amount
     * @return mixed
     */
    public function getConvertedPrice($amount)
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * Get Billing Maximum Cycle Label
     * @return string
     */
    public function getBillingMaxCyclesLabel()
    {
        return ($this->getSubscriptionData()->getBillingMaxCycles()) ?
            __('%1 times(s)', $this->getSubscriptionData()->getBillingMaxCycles()) :
            __('Repeats until failed or canceled');
    }

    /**
     * Get Trial Maximum Cycle Label
     * @return string
     */
    public function getTrialMaxCycleLabel()
    {
        $trialLabel = ($this->getSubscriptionData()->getTrialMaxCycle()) ?
            __('%1 times(s)', $this->getSubscriptionData()->getTrialMaxCycle()) :
            __('Repeats until failed or canceled');
        return $trialLabel;
    }

    /**
     * @param object $item CartItem
     * @param array $request
     * @return boolean
     */
    public function isSubscribed($item)
    {
        $request = $item->getBuyRequest()->getData();
        return $this->checkProductRequest($request, $item);
    }

    /**
     * Check product is subscription
     * @param object $item
     * @return boolean
     */
    public function isSubscriptionProduct($item)
    {
        return ($item->getProduct()->getIsSubscription() && $item->getProduct()->getSubscriptionType() == PurchaseOption::SUBSCRIPTION);
    }

    /**
     * Is Requested Product Subscription
     * @param null $param
     * @param null $item
     * @return bool
     */
    public function checkProductRequest($param = null, $item = null)
    {
        if (isset($param) &&
                isset($param['options']['_1']) &&
                $param['options']['_1'] == PurchaseOption::SUBSCRIPTION
        ) {
            return true;
        } elseif ($item && $this->isSubscriptionProduct($item)) {
            return true;
        }
        return false;
    }

    /**
     * Validating Item on Add to cart time
     * @param Object $item
     * @return mixed
     * @throws LocalizedException
     */
    public function validate($item, $cartRequest)
    {
        $this->validateQty($item);
        $this->updateSubscriptionDateByToday($item);
        $this->validateSubscriptionDate($item, $cartRequest);
    }

    /**
     * Validate Item Qty
     *
     * @param Object $item
     * @throws LocalizedException
     */
    public function validateQty($item)
    {
        $allowedQty = $this->subscriptionHelper->getMaxAllowedQty();

        if (!$item->getParentItem()) {
            $itemQty = $item->getQty();

            if ($allowedQty && $itemQty > $allowedQty) {
                $errorMessage = $this->subscriptionHelper->getQtyErrorMessage();
                throw new LocalizedException($errorMessage);
            }
        }
    }

    /**
     * Check current product
     * subscription date is greater than today
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isFutureSubscription($product)
    {
        if ($product->getCustomOption('info_buyRequest')
            && $product->getCustomOption('info_buyRequest')->getValue()
            && !$product->getSkipFutureSubscriptionValidation()
        ) {
            $infoBuyRequest = $this->serialize->unserialize($product->getCustomOption('info_buyRequest')->getValue());
            if ($this->isFutureItem($infoBuyRequest)) {
                return true;
            }
        }

        return false;
    }

    public function isFutureItem($request = null)
    {
        if ($request && is_object($request)) {
            $request = $request->getData();
        }

        if ($this->checkProductRequest($request) && count($request) > 0) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string) isset($request['subscription_start_date']) ? $request['subscription_start_date'] : $currentDate;
            $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));
            if ($currentDate !== $requestDate && $subscriptionStartDate > $currentDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create Subscription Profile
     *
     * @param $order
     * @return bool
     */
    public function create($order)
    {
        $result = false;

        if ($order && !$order->getQuoteId()) {
            return $result;
        }

        $quote = $this->cartRepositoryFactory->create()->get($order->getQuoteId());
        $orderService = $this->orderServiceFactory->create();

        if (!$order->getSubscriptionParentId()) {
            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItemId() != null) {
                    continue;
                }

                if ($this->isSubscribed($item)) {
                    $orderService->createSubscriptionProfile($order, $item);
                    $result = true;
                }
            }
        }

        return $result;
    }

    public function getProductModel($parentId)
    {
        if (!$this->productModel) {
            $this->productModel = $this->productModelFactory->create();
        }

        if (!$this->currentProductObject) {
            $this->currentProductObject = $this->productModel->load($parentId);
        } elseif ($this->currentProductObject && $this->currentProductObject->getId() != $parentId) {
            $this->currentProductObject = $this->productModel->load($parentId);
        }

        return $this->currentProductObject;
    }

    public function getGroupProductIdFromRequest($request = [])
    {
        $id = null;
        if ($request
            && isset($request['super_product_config'])
            && isset($request['super_product_config']['product_type'])
            && $request['super_product_config']['product_type'] == 'grouped'
        ) {
            $id = $request['super_product_config']['product_id'];
        }
        return $id;
    }

    public function getGroupedParentId($product)
    {
        $parentId = null;
        if ($product->getCustomOption('info_buyRequest')) {
            $requestValue = $product->getCustomOption('info_buyRequest')->getValue();
            $request = $this->serialize->unserialize($requestValue);
            $parentId = $this->getGroupProductIdFromRequest($request);
        }
        return $parentId;
    }

    /**
     * Validate Subscription Date
     * @param $item
     * @param array $param
     * @throws LocalizedException
     */
    public function validateSubscriptionDate($item, $param = [])
    {
        $request = $item->getBuyRequest()->getData();

        if ($request) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string) isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
            $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));

            if ($requestDate && $currentDate > $subscriptionStartDate) {
                $this->getCurrentDateOnCartActive($item);
                /*throw new LocalizedException(__('Subscription start date must be greater than today.'));*/
            }
        }
    }

    /**
     * Calculate Max billing cycle from end date
     *
     * @param $request
     * @return false|float|int|mixed|string|null
     * @throws LocalizedException
     * @since 200.7.0
     */
    public function endCycleCalculation($request)
    {
        $endType  = $request['end_type'];
        $endCycle = $request['subscription_end_cycle'];

        if ($endType == Subscription::END_TYPE_CYCLE) {
            return $endCycle;
        } elseif ($endType == Subscription::END_TYPE_DATE) {
            return $this->endDateCalculation($request);
        }
        return null;
    }


    /**
     * Calculate End Date
     *
     * @param $request
     * @return float
     * @throws LocalizedException
     * @since 200.7.0
     */
    private function endDateCalculation($request)
    {
        if(isset($request['billing_period']) && $this->getBillingPeriodType() == BillingPeriodBy::CUSTOMER){
            $subscriptionInterval = $this->getSubscriptionInterval($request['billing_period']);
        } else {
            $billingPeriod = $this->getBillingPeriod();
            $subscriptionInterval = $this->getSubscriptionInterval($billingPeriod);
        }

        $billingPeriod = $subscriptionInterval['interval_type'];
        $billingFrequency = $subscriptionInterval['no_of_interval'];
        $requestDate = (string)isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
        $subscriptionStartDate = strtotime($requestDate);

        $endDate = (string)isset($request['subscription_end_date']) ? $request['subscription_end_date'] : '';
        $subscriptionEndDate = strtotime($endDate);

        $dateDiff = $subscriptionEndDate - $subscriptionStartDate;
        $dateDiff = round($dateDiff / (60 * 60 * 24));

        $year1 = date('Y', $subscriptionStartDate);
        $year2 = date('Y', $subscriptionEndDate);
        $month1 = date('m', $subscriptionStartDate);
        $month2 = date('m', $subscriptionEndDate);

        switch ($billingPeriod) {
            case 'day':
                $finalCycle = $dateDiff / $billingFrequency;
                break;
            case 'week':
                $finalCycle = ($dateDiff / $billingFrequency) / 7;
                break;
            case 'month':
                $finalCycle = (($year2 - $year1) * 12) + ($month2 - $month1);
                break;
            case 'year':
                $finalCycle = $year2 - $year1;
                break;
            default:
                $finalCycle = '';
        }
        $finalCycle = floor($finalCycle);

        if($finalCycle < 1){
            throw new LocalizedException(
                __('Subscription end date does not meet with selected date and frequency.')
            );
        }
        return $finalCycle;
    }

    /**
     * @param $item
     */
    public function updateSubscriptionDateByToday($item)
    {
        if (!$item->getInfoBuyRequest()) {
            return;
        }
        $requestOptionData = $item->getOptionByCode('info_buyRequest')->getData();
        $additionalOptionData = $item->getOptionByCode('additional_options')->getData();
        $request = $requestOptionData['value'];
        $options = $additionalOptionData['value'];
        if ($options) {
            $options = $this->serialize->unserialize($options);
        }
        if ($request) {
            $request = $this->serialize->unserialize($request);
        }
        if ($request) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $requestDate = (string)isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
            $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));

            if ($requestDate && $currentDate > $subscriptionStartDate) {
                /** Info buyRequest Change */
                $request['subscription_start_date'] = $currentDate;

                /** Cart Option Change md_sub_start_date */
                $options[2]['value'] = $currentDate;
                /** Update both option value */
                $requestOptionData['value'] = $this->serialize->serialize($request);
                $item->addOption($requestOptionData);
                $additionalOptionData['value'] = $this->serialize->serialize($options);
                $item->addOption($additionalOptionData);
                $item->saveItemOptions();
            }
        }
    }

    /**
     * @param $item
     * @throws LocalizedException
     */
    public function getCurrentDateOnCartActive($item)
    {
        try {
            $requestOptionData = $item->getOptionByCode('info_buyRequest')->getData();
            $additionalOptionData = null;
            if ($item->getOptionByCode('additional_options')) {
                $additionalOptionData = $item->getOptionByCode('additional_options')->getData();
            }
            $request = $requestOptionData['value'];
            $options = $additionalOptionData['value'];
            if ($options) {
                $options = $this->serialize->unserialize($options);
            }
            if ($request) {
                $request = $this->serialize->unserialize($request);
            }
            if ($request) {
                $currentDate = $this->timezone->date()->format('Y-m-d');
                $requestDate = (string)isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
                $subscriptionStartDate = date('Y-m-d', strtotime($requestDate));
                if ($requestDate && $currentDate > $subscriptionStartDate) {
                    /** Info buyRequest Change */
                    $request['subscription_start_date'] = $currentDate;
                    if (isset($options[2]['code']) && $options[2]['code'] == 'md_sub_start_date') {
                        $options[2]['value'] = $currentDate;
                    }
                    /** Update both option value */
                    $requestOptionData['value'] = $this->serialize->serialize($request);
                    $item->addOption($requestOptionData);
                    $additionalOptionData['value'] = $this->serialize->serialize($options);
                    $item->addOption($additionalOptionData);
                    $item->saveItemOptions();
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while get current date.')
            );
        }
    }
}
