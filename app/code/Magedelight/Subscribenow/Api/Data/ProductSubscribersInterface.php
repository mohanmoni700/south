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

namespace Magedelight\Subscribenow\Api\Data;

interface ProductSubscribersInterface
{
    const SUBSCRIPTION_ID          = 'subscription_id';
    const PROFILE_ID               = 'profile_id';
    const CUSTOMER_ID              = 'customer_id';
    const PRODUCT_ID               = 'product_id';
    const PRODUCT_SKU              = 'product_sku';
    const QTY_SUBSCRIBED           = 'qty_subscribed';
    const SUBSCRIBER_NAME          = 'subscriber_name';
    const SUBSCRIBER_EMAIL         = 'subscriber_email';
    const STORE_ID                 = 'store_id';
    const PAYMENT_METHOD_CODE      = 'payment_method_code';
    const SUBSCRIPTION_START_DATE  = 'subscription_start_date';
    const SUSPENSION_THRESHOLD     = 'suspension_threshold';
    const BILLING_PERIOD_LABEL     = 'billing_period_label';
    const BILLING_PERIOD           = 'billing_period';
    const BILLING_FREQUENCY        = 'billing_frequency';
    const PERIOD_MAX_CYCLES        = 'period_max_cycles';
    const BILLING_AMOUNT           = 'billing_amount';
    const TRIAL_PERIOD_LABEL       = 'trial_period_label';
    const TRIAL_PERIOD_UNIT        = 'trial_period_unit';
    const TRIAL_PERIOD_FREQUENCY   = 'trial_period_frequency';
    const TRIAL_PERIOD_MAX_CYCLE   = 'trial_period_max_cycle';
    const TRIAL_BILLING_AMOUNT     = 'trial_billing_amount';
    const CURRENCY_CODE            = 'currency_code';
    const SHIPPING_AMOUNT          = 'shipping_amount';
    const TAX_AMOUNT               = 'tax_amount';
    const INITIAL_AMOUNT           = 'initial_amount';
    const DISCOUNT_AMOUNT          = 'discount_amount';
    const ORDER_INFO               = 'order_info';
    const ORDER_ITEM_INFO          = 'order_item_info';
    const BILLING_ADDRESS_INFO     = 'billing_address_info';
    const SHIPPING_ADDRESS_INFO    = 'shipping_address_info';
    const ADDITIONAL_INFO          = 'additional_info';
    const SUBSCRIPTION_STATUS      = 'subscription_status';
    const INITIAL_ORDER            = 'initial_order';
    const SUBSCRIPTION_ITEM_INFO   = 'subscription_item_info';
    const CREATED_AT               = 'created_at';
    const UPDATED_AT               = 'updated_at';
    const NEXT_OCCURRENCE_DATE     = 'next_occurrence_date';
    const LAST_BILL_DATE           = 'last_bill_date';
    const TRIAL_COUNT              = 'trial_count';
    const PAYMENT_TOKEN            = 'payment_token';
    const TOTAL_BILL_COUNT         = 'total_bill_count';
    const BASE_CURRENCY_CODE       = 'base_currency_code';
    const BASE_BILLING_AMOUNT      = 'base_billing_amount';
    const BASE_TRIAL_BILLING_AMOUNT= 'base_trial_billing_amount';
    const BASE_SHIPPING_AMOUNT     = 'base_shipping_amount';
    const BASE_TAX_AMOUNT          = 'base_tax_amount';
    const BASE_INITIAL_AMOUNT      = 'base_initial_amount';
    const BASE_DISCOUNT_AMOUNT     = 'base_discount_amount';
    const INITIAL_ORDER_ID         = 'initial_order_id';
    const BILLING_ADDRESS_ID       = 'billing_address_id';
    const SHIPPING_ADDRESS_ID      = 'shipping_address_id';
    const IS_TRIAL                 = 'is_trial';
    const SHIPPING_METHOD_CODE     = 'shipping_method_code';
    const PRODUCT_NAME             = 'product_name';
    const PAYMENT_TITLE            =  'payment_title';
    const PARENT_PRODUCT_ID        =  'parent_product_id';
    const BILLING_FREQUENCY_CYCLE  =  'billing_frequency_cycle';
    const IS_UPDATE_BILLING_FREQUENCY  =  'is_update_billing_frequency';
    const SUBSCRIPTION_END_DATE     = 'subscription_end_date';
    
    /**
     * Get Subscriber ID
     * @return int
     */
    public function getSubscriptionId();
    
    /**
     * Set Subscriber ID
     *
     * @param $subscriptionId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriptionId($subscriptionId);
    
    /**
     * Get Profile ID
     * @return mixed
     */
    public function getProfileId();
    
    /**
     * Set Profile ID
     *
     * @param $profileId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setProfileId($profileId);
    
    /**
     * Get Customer ID
     * @return int
     */
    public function getCustomerId();
    
    /**
     * Set Customer ID
     *
     * @param $customerId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setCustomerId($customerId);
    
    /**
     * Get Product ID
     * @return int
     */
    public function getProductId();
    
    /**
     * Set Product ID
     *
     * @param $productId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setProductId($productId);

    /**
     * Get Product SKU
     * @return string
     */
    public function getProductSku();

    /**
     * Set Product SKU
     *
     * @param $sku
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setProductSku($sku);
    
    /**
     * Get Subscriber Name
     * @return string
     */
    public function getSubscriberName();
    
    /**
     * Set Subscriber Name
     *
     * @param $subscriberName
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriberName($subscriberName);
    
    /**
     * Get Subscriber Email
     * @return string
     */
    public function getSubscriberEmail();
    
    /**
     * Set Subscriber Email
     *
     * @param $subscriberEmail
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriberEmail($subscriberEmail);
    
    /**
     * Get Store ID
     * @return int
     */
    public function getStoreId();
    
    /**
     * Set Store ID
     *
     * @param $storeId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setStoreId($storeId);
    
    /**
     * Get Payment Method Code
     * @return string
     */
    public function getPaymentMethodCode();
    
    /**
     * Set Payment Method Code
     *
     * @param $paymentMethodCode
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setPaymentMethodCode($paymentMethodCode);
    
    /**
     * Get Subscription Start Date
     * @return mixed
     */
    public function getSubscriptionStartDate();
    
    /**
     * Set Subscription Start Date
     *
     * @param $subscriptionStartDate
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriptionStartDate($subscriptionStartDate);
    
    /**
     * Get Suspension Threshold
     * @return int
     */
    public function getSuspensionThreshold();
    
    /**
     * Set Suspension Threshold
     *
     * @param $suspensionThreshold
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSuspensionThreshold($suspensionThreshold);
    
    /**
     * Get Billing Period Label
     * @return string
     */
    public function getBillingPeriodLabel();
    
    /**
     * Set Billing Period Label
     *
     * @param $billingPeriodLabel
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingPeriodLabel($billingPeriodLabel);
    
    /**
     * Get Billing Period Key
     * @return int
     */
    public function getBillingPeriod();
    
    /**
     * Set Billing Period Key
     *
     * @param $billingPeriod
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingPeriod($billingPeriod);
    
    /**
     * Get Billing Frequency
     * On which time occurred with billing period
     * @return int
     */
    public function getBillingFrequency();
    
    /**
     * Set Billing Frequency
     *
     * @param $billingFrequency
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingFrequency($billingFrequency);
    
    /**
     * Get Period Max Cycles
     * @return int
     */
    public function getPeriodMaxCycles();
    
    /**
     * Set Period Max Cycles
     *
     * @param $periodMaxCycles
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setPeriodMaxCycles($periodMaxCycles);
    
    /**
     * Get Billing Amount
     * @return double
     */
    public function getBillingAmount();
    
    /**
     * Set Billing Amount
     *
     * @param $billingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingAmount($billingAmount);
    
    /**
     * Get Trial Period Label
     * @return string
     */
    public function getTrialPeriodLabel();
    
    /**
     * Set Trial Period Label
     *
     * @param $trialPeriodLabel
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialPeriodLabel($trialPeriodLabel);
    
    /**
     * Get Trial Period Unit
     * @return mixed
     */
    public function getTrialPeriodUnit();
    
    /**
     * Set Trial Period Unit
     *
     * @param $trialPeriodUnit
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialPeriodUnit($trialPeriodUnit);
    
    /**
     * Get Trial Period Frequency
     * On which time occurred with billing period
     * @return int
     */
    public function getTrialPeriodFrequency();
    
    /**
     * Set Trial Period Frequency
     *
     * @param $trialPeriodFrequency
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialPeriodFrequency($trialPeriodFrequency);
    
    /**
     * Get Trial Period Max Cycles
     * @return int
     */
    public function getTrialPeriodMaxCycles();
    
    /**
     * Set Trial Period Max Cycles
     *
     * @param $trialPeriodMaxCycles
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialPeriodMaxCycles($trialPeriodMaxCycles);
    
    /**
     * Get Trial Billing Amount
     * @return mixed
     */
    public function getTrialBillingAmount();
    
    /**
     * Set Trial Billing Amount
     *
     * @param $trialBillingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialBillingAmount($trialBillingAmount);
    
    /**
     * Get Currency Code
     * @return string
     */
    public function getCurrencyCode();
    
    /**
     * Set Currency Code
     *
     * @param $currencyCode
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setCurrencyCode($currencyCode);
    
    /**
     * Get Shipping Amount
     * @return mixed
     */
    public function getShippingAmount();
    
    /**
     * Set Shipping Amount
     *
     * @param $shippingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setShippingAmount($shippingAmount);
    
    /**
     * Get Tax Amount
     * @return mixed
     */
    public function getTaxAmount();
    
    /**
     * Set Tax Amount
     *
     * @param $taxAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTaxAmount($taxAmount);
    
    /**
     * Get Initial Amount
     * @return mixed
     */
    public function getInitialAmount();
    
    /**
     * Set Initial Amount
     *
     * @param $initialAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setInitialAmount($initialAmount);
    
    /**
     * Get Discount Amount
     * @return mixed
     */
    public function getDiscountAmount();
    
    /**
     * Set Discount Amount
     *
     * @param $discountAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setDiscountAmount($discountAmount);
    
    /**
     * Get Order Info
     * @return mixed
     */
    public function getOrderInfo();
    
    /**
     * Set Order Info
     *
     * @param $orderInfo
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setOrderInfo($orderInfo);
    
    /**
     * Get Order Item Info
     * @return mixed
     */
    public function getOrderItemInfo();
    
    /**
     * Set Order Item Info
     *
     * @param $orderItemInfo
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setOrderItemInfo($orderItemInfo);
    
    /**
     * Get Additional Info
     * @param string
     * @return mixed
     */
    public function getAdditionalInfo($key = null);
    
    /**
     * Set Additional Info
     *
     * @param $additionalInfo
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setAdditionalInfo($additionalInfo);
    
    /**
     * Get Subscription Status
     * @return mixed
     */
    public function getSubscriptionStatus();
    
    /**
     * Set Subscription Status
     *
     * @param $subscriptionStatus
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriptionStatus($subscriptionStatus);
    
    /**
     * Get Subscription Item Info
     * @return mixed
     */
    public function getSubscriptionItemInfo();
    
    /**
     * Set Subscription Item Info
     *
     * @param $subscriptionItemInfo
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriptionItemInfo($subscriptionItemInfo);
    
    /**
     * Get Created At
     * @return mixed
     */
    public function getCreatedAt();
    
    /**
     * Set Created At
     *
     * @param $createdAt
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Get Updated At
     * @return mixed
     */
    public function getUpdatedAt();
    
    /**
     * Set Updated At
     *
     * @param $updatedAt
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setUpdatedAt($updatedAt);
    
    /**
     * Get Next Occurrence Date
     * @return mixed
     */
    public function getNextOccurrenceDate();
    
    /**
     * Set Next Occurrence Date
     *
     * @param $nextOccurrenceDate
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setNextOccurrenceDate($nextOccurrenceDate);
    
    /**
     * Get Last Bill Date
     * @return mixed
     */
    public function getLastBillDate();
    
    /**
     * Set Last Bill Date
     *
     * @param $lastBillDate
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setLastBillDate($lastBillDate);
    
    /**
     * Get Trial Count
     * @return int
     */
    public function getTrialCount();
    
    /**
     * Set Trial Count
     *
     * @param $trialCount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTrialCount($trialCount);
    
    /**
     * Get Payment Token
     * @return mixed
     */
    public function getPaymentToken();
    
    /**
     * Set Payment Token
     *
     * @param $paymentToken
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setPaymentToken($paymentToken);
    
    /**
     * Get Total Bill Count
     * @return int
     */
    public function getTotalBillCount();
    
    /**
     * Set Total Bill Count
     *
     * @param $totalBillCount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setTotalBillCount($totalBillCount);
    
    /**
     * Get Base Currency Code
     * @return string
     */
    public function getBaseCurrencyCode();
    
    /**
     * Set Base Currency Code
     *
     * @param $baseCurrencyCode
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseCurrencyCode($baseCurrencyCode);
    
    /**
     * Get Base Billing Amount
     * @return mixed
     */
    public function getBaseBillingAmount();
    
    /**
     * Set Base Billing Amount
     *
     * @param $baseBillingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseBillingAmount($baseBillingAmount);
    
    /**
     * Get Base Trial Billing Amount
     * @return mixed
     */
    public function getBaseTrialBillingAmount();
    
    /**
     * Set Base Trial Billing Amount
     *
     * @param $baseTrialBillingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseTrialBillingAmount($baseTrialBillingAmount);
    
    /**
     * Get Base Shipping Amount
     * @return mixed
     */
    public function getBaseShippingAmount();
    
    /**
     * Set Base Shipping Amount
     *
     * @param $baseShippingAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseShippingAmount($baseShippingAmount);
    
    /**
     * Get Base Tax Amount
     * @return mixed
     */
    public function getBaseTaxAmount();
    
    /**
     * Set Base Tax Amount
     *
     * @param $baseTaxAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseTaxAmount($baseTaxAmount);
    
    /**
     * Get Base Initial Amount
     * @return mixed
     */
    public function getBaseInitialAmount();
    
    /**
     * Set Base Initial Amount
     *
     * @param $baseInitialAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseInitialAmount($baseInitialAmount);
    
    /**
     * Get Base Discount Amount
     * @return mixed
     */
    public function getBaseDiscountAmount();
    
    /**
     * Set Base Discount Amount
     *
     * @param $baseDiscountAmount
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBaseDiscountAmount($baseDiscountAmount);
    
    /**
     * Get Order Increment ID
     * @return mixed
     */
    public function getInitialOrderId();
    
    /**
     * Set Order Increment ID
     *
     * @param $initialOrderId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setInitialOrderId($initialOrderId);
    
    /**
     * Get Billing Address ID
     * @return mixed
     */
    public function getBillingAddressId();
    
    /**
     * Set Billing Address ID
     *
     * @param $billingAddressId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingAddressId($billingAddressId);
    
    /**
     * Get Shipping Address ID
     * @return mixed
     */
    public function getShippingAddressId();
    
    /**
     * Set Shipping Address ID
     *
     * @param $shippingAddressId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setShippingAddressId($shippingAddressId);
    
    /**
     * Get Is Trial
     * @return mixed
     */
    public function getIsTrial();
    
    /**
     * Set Is Trial
     *
     * @param $isTrial
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setIsTrial($isTrial);
    
    /**
     * Get Shipping Method Code
     * @return mixed
     */
    public function getShippingMethodCode();
    
    /**
     * Set Shipping Method Code
     *
     * @param $shippingMethodCode
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setShippingMethodCode($shippingMethodCode);
    
    /**
     * Get Product Name
     * @return mixed
     */
    public function getProductName();
    
    /**
     * Set Product Name
     *
     * @param $productName
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setProductName($productName);
    
    /**
     * Get Payment Method Title
     * @return mixed
     */
    public function getPaymentTitle();
    
    /**
     * Set Payment Method Title
     *
     * @param $paymentTitle
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setPaymentTitle($paymentTitle);

    /**
     * Get Parent Product ID
     *
     * @return int|null
     */
    public function getParentProductId();

    /**
     * Set Parent Product ID
     *
     * @param $parentProductId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setParentProductId($parentProductId);

    /**
     * Get Billing Frequency Cycle
     * @return mixed|null
     */
    public function getBillingFrequencyCycle();

    /**
     * Set Billing Frequency Cycle
     *
     * @param $billingFrequencyCycle
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setBillingFrequencyCycle($billingFrequencyCycle);

    /**
     * Can Update Billing Cycle
     * @return mixed|null
     */
    public function getIsUpdateBillingFrequency();

    /**
     * Set Update Billing Cycle
     *
     * @param $isUpdateBillingFrequency
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setIsUpdateBillingFrequency($isUpdateBillingFrequency);

    /**
     * Get Subscription End Date
     * @return mixed
     * @since 200.7.0
     */
    public function getSubscriptionEndDate();

    /**
     * Set Subscription End Date
     *
     * @since 200.7.0
     * @param $subscriptionEndDate
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     */
    public function setSubscriptionEndDate($subscriptionEndDate);
}
