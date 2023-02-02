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

namespace Magedelight\Subscribenow\Model\Data;

use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers as ProductSubscribersResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class ProductSubscribers
 *
 * @since 200.5.0
 * @package Magedelight\Subscribenow\Model\Data
 */
class ProductSubscribers extends AbstractModel implements ProductSubscribersInterface
{
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ProductSubscribersResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * @inheritDoc
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function getQtySubscribed()
    {
        return $this->getData(self::QTY_SUBSCRIBED);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * @inheritDoc
     */
    public function setQtySubscribed($qtySubscribed)
    {
        return $this->setData(self::QTY_SUBSCRIBED, $qtySubscribed);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriberName()
    {
        return $this->getData(self::SUBSCRIBER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriberName($subscriberName)
    {
        return $this->setData(self::SUBSCRIBER_NAME, $subscriberName);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriberEmail()
    {
        return $this->getData(self::SUBSCRIBER_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriberEmail($subscriberEmail)
    {
        return $this->setData(self::SUBSCRIBER_EMAIL, $subscriberEmail);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodCode()
    {
        return $this->getData(self::PAYMENT_METHOD_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethodCode($paymentMethodCode)
    {
        return $this->setData(self::PAYMENT_METHOD_CODE, $paymentMethodCode);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionStartDate()
    {
        return $this->getData(self::SUBSCRIPTION_START_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionStartDate($subscriptionStartDate)
    {
        return $this->setData(self::SUBSCRIPTION_START_DATE, $subscriptionStartDate);
    }

    /**
     * @inheritDoc
     */
    public function getSuspensionThreshold()
    {
        return $this->getData(self::SUSPENSION_THRESHOLD);
    }

    /**
     * @inheritDoc
     */
    public function setSuspensionThreshold($suspensionThreshold)
    {
        return $this->setData(self::SUSPENSION_THRESHOLD, $suspensionThreshold);
    }

    /**
     * @inheritDoc
     */
    public function getBillingPeriodLabel()
    {
        return $this->getData(self::BILLING_PERIOD_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setBillingPeriodLabel($billingPeriodLabel)
    {
        return $this->setData(self::BILLING_PERIOD_LABEL, $billingPeriodLabel);
    }

    /**
     * @inheritDoc
     */
    public function getBillingPeriod()
    {
        return $this->getData(self::BILLING_PERIOD);
    }

    /**
     * @inheritDoc
     */
    public function setBillingPeriod($billingPeriod)
    {
        return $this->setData(self::BILLING_PERIOD, $billingPeriod);
    }

    /**
     * Get Billing Frequency
     * On which time occurred with billing period
     * @return int
     */
    public function getBillingFrequency()
    {
        return $this->getData(self::BILLING_FREQUENCY);
    }

    /**
     * @inheritDoc
     */
    public function setBillingFrequency($billingFrequency)
    {
        return $this->setData(self::BILLING_FREQUENCY, $billingFrequency);
    }

    /**
     * @inheritDoc
     */
    public function getPeriodMaxCycles()
    {
        return $this->getData(self::PERIOD_MAX_CYCLES);
    }

    /**
     * @inheritDoc
     */
    public function setPeriodMaxCycles($periodMaxCycles)
    {
        return $this->setData(self::PERIOD_MAX_CYCLES, $periodMaxCycles);
    }

    /**
     * @inheritDoc
     */
    public function getBillingAmount()
    {
        return $this->getData(self::BILLING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBillingAmount($billingAmount)
    {
        return $this->setData(self::BILLING_AMOUNT, $billingAmount);
    }

    /**
     * @inheritDoc
     */
    public function getTrialPeriodLabel()
    {
        return $this->getData(self::TRIAL_PERIOD_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setTrialPeriodLabel($trialPeriodLabel)
    {
        return $this->setData(self::TRIAL_PERIOD_LABEL, $trialPeriodLabel);
    }

    /**
     * @inheritDoc
     */
    public function getTrialPeriodUnit()
    {
        return $this->getData(self::TRIAL_PERIOD_UNIT);
    }

    /**
     * @inheritDoc
     */
    public function setTrialPeriodUnit($trialPeriodUnit)
    {
        return $this->setData(self::TRIAL_PERIOD_UNIT, $trialPeriodUnit);
    }

    /**
     * Get Trial Period Frequency
     * On which time occurred with billing period
     * @return int
     */
    public function getTrialPeriodFrequency()
    {
        return $this->getData(self::TRIAL_PERIOD_FREQUENCY);
    }

    /**
     * @inheritDoc
     */
    public function setTrialPeriodFrequency($trialPeriodFrequency)
    {
        return $this->setData(self::TRIAL_PERIOD_FREQUENCY, $trialPeriodFrequency);
    }

    /**
     * @inheritDoc
     */
    public function getTrialPeriodMaxCycles()
    {
        return $this->getData(self::TRIAL_PERIOD_MAX_CYCLE);
    }

    /**
     * @inheritDoc
     */
    public function setTrialPeriodMaxCycles($trialPeriodMaxCycles)
    {
        return $this->setData(self::TRIAL_PERIOD_MAX_CYCLE, $trialPeriodMaxCycles);
    }

    /**
     * @inheritDoc
     */
    public function getTrialBillingAmount()
    {
        return $this->getData(self::TRIAL_BILLING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTrialBillingAmount($trialBillingAmount)
    {
        return $this->setData(self::TRIAL_BILLING_AMOUNT, $trialBillingAmount);
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * @inheritDoc
     */
    public function getShippingAmount()
    {
        return $this->getData(self::SHIPPING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setShippingAmount($shippingAmount)
    {
        return $this->setData(self::SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     * @inheritDoc
     */
    public function getTaxAmount()
    {
        return $this->getData(self::TAX_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTaxAmount($taxAmount)
    {
        return $this->setData(self::TAX_AMOUNT, $taxAmount);
    }

    /**
     * @inheritDoc
     */
    public function getInitialAmount()
    {
        return $this->getData(self::INITIAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setInitialAmount($initialAmount)
    {
        return $this->setData(self::INITIAL_AMOUNT, $initialAmount);
    }

    /**
     * @inheritDoc
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * @inheritDoc
     */
    public function getOrderInfo()
    {
        return $this->getData(self::ORDER_INFO);
    }

    /**
     * @inheritDoc
     */
    public function setOrderInfo($orderInfo)
    {
        return $this->setData(self::ORDER_INFO, $orderInfo);
    }

    /**
     * @inheritDoc
     */
    public function getOrderItemInfo($key = null)
    {
        $data = $this->getData(self::ORDER_ITEM_INFO);
        if ($key && is_array($data)) {
            return !empty($data[$key]) ? $data[$key] : null;
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function setOrderItemInfo($orderItemInfo)
    {
        return $this->setData(self::ORDER_ITEM_INFO, $orderItemInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInfo($key = null)
    {
        $data = $this->getData(self::ADDITIONAL_INFO);
        if ($key && is_array($data)) {
            return !empty($data[$key]) ? $data[$key] : null;
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalInfo($additionalInfo)
    {
        return $this->setData(self::ADDITIONAL_INFO, $additionalInfo);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionStatus()
    {
        return $this->getData(self::SUBSCRIPTION_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionStatus($subscriptionStatus)
    {
        return $this->setData(self::SUBSCRIPTION_STATUS, $subscriptionStatus);
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionItemInfo()
    {
        return $this->getData(self::SUBSCRIPTION_ITEM_INFO);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionItemInfo($subscriptionItemInfo)
    {
        return $this->setData(self::SUBSCRIPTION_ITEM_INFO, $subscriptionItemInfo);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getNextOccurrenceDate()
    {
        return $this->getData(self::NEXT_OCCURRENCE_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setNextOccurrenceDate($nextOccurrenceDate)
    {
        return $this->setData(self::NEXT_OCCURRENCE_DATE, $nextOccurrenceDate);
    }

    /**
     * @inheritDoc
     */
    public function getLastBillDate()
    {
        return $this->getData(self::LAST_BILL_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setLastBillDate($lastBillDate)
    {
        return $this->setData(self::LAST_BILL_DATE, $lastBillDate);
    }

    /**
     * @inheritDoc
     */
    public function getTrialCount()
    {
        return $this->getData(self::TRIAL_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTrialCount($trialCount)
    {
        return $this->setData(self::TRIAL_COUNT, $trialCount);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentToken()
    {
        return $this->getData(self::PAYMENT_TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentToken($paymentToken)
    {
        return $this->setData(self::PAYMENT_TOKEN, $paymentToken);
    }

    /**
     * @inheritDoc
     */
    public function getTotalBillCount()
    {
        return $this->getData(self::TOTAL_BILL_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTotalBillCount($totalBillCount)
    {
        return $this->setData(self::TOTAL_BILL_COUNT, $totalBillCount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::BASE_CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * @inheritDoc
     */
    public function getBaseBillingAmount()
    {
        return $this->getData(self::BASE_BILLING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseBillingAmount($baseBillingAmount)
    {
        return $this->setData(self::BASE_BILLING_AMOUNT, $baseBillingAmount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseTrialBillingAmount()
    {
        return $this->getData(self::BASE_TRIAL_BILLING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseTrialBillingAmount($baseTrialBillingAmount)
    {
        return $this->setData(self::BASE_TRIAL_BILLING_AMOUNT, $baseTrialBillingAmount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseShippingAmount($baseShippingAmount)
    {
        return $this->setData(self::BASE_SHIPPING_AMOUNT, $baseShippingAmount);
    }

    /**
     * Get Base Tax Amount
     * @return mixed
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(self::BASE_TAX_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        return $this->setData(self::BASE_TAX_AMOUNT, $baseTaxAmount);
    }

    /**
     * Get Base Initial Amount
     * @return mixed
     */
    public function getBaseInitialAmount()
    {
        return $this->getData(self::BASE_INITIAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseInitialAmount($baseInitialAmount)
    {
        return $this->setData(self::BASE_INITIAL_AMOUNT, $baseInitialAmount);
    }

    /**
     * @inheritDoc
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        return $this->setData(self::BASE_DISCOUNT_AMOUNT, $baseDiscountAmount);
    }

    /**
     * @inheritDoc
     */
    public function getInitialOrderId()
    {
        return $this->getData(self::INITIAL_ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setInitialOrderId($initialOrderId)
    {
        return $this->setData(self::INITIAL_ORDER_ID, $initialOrderId);
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddressId()
    {
        return $this->getData(self::BILLING_ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBillingAddressId($billingAddressId)
    {
        return $this->setData(self::BILLING_ADDRESS_ID, $billingAddressId);
    }

    /**
     * @inheritDoc
     */
    public function getShippingAddressId()
    {
        return $this->getData(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setShippingAddressId($shippingAddressId)
    {
        return $this->setData(self::SHIPPING_ADDRESS_ID, $shippingAddressId);
    }

    /**
     * @inheritDoc
     */
    public function getIsTrial()
    {
        return $this->getData(self::IS_TRIAL);
    }

    /**
     * @inheritDoc
     */
    public function setIsTrial($isTrial)
    {
        return $this->setData(self::IS_TRIAL, $isTrial);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethodCode()
    {
        return $this->getData(self::SHIPPING_METHOD_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethodCode($shippingMethodCode)
    {
        return $this->setData(self::SHIPPING_METHOD_CODE, $shippingMethodCode);
    }

    /**
     * @inheritDoc
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTitle()
    {
        return $this->getData(self::PAYMENT_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentTitle($paymentTitle)
    {
        return $this->setData(self::PAYMENT_TITLE, $paymentTitle);
    }

    /**
     * @inheritDoc
     */
    public function getParentProductId()
    {
        return $this->getData(self::PARENT_PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentProductId($parentProductId)
    {
        return $this->setData(self::PARENT_PRODUCT_ID, $parentProductId);
    }

    /**
     * @inheritDoc
     */
    public function getBillingFrequencyCycle()
    {
        return $this->getData(self::BILLING_FREQUENCY_CYCLE);
    }

    /**
     * @inheritDoc
     */
    public function setBillingFrequencyCycle($billingFrequencyCycle)
    {
        return $this->setData(self::BILLING_FREQUENCY_CYCLE, $billingFrequencyCycle);
    }

    /**
     * @inheritDoc
     */
    public function getIsUpdateBillingFrequency()
    {
        return $this->getData(self::IS_UPDATE_BILLING_FREQUENCY);
    }

    /**
     * @inheritDoc
     */
    public function setIsUpdateBillingFrequency($isUpdateBillingFrequency)
    {
        return $this->setData(self::IS_UPDATE_BILLING_FREQUENCY, $isUpdateBillingFrequency);
    }

    /**
     * @inheritDoc
     * @since 200.7.0
     */
    public function getSubscriptionEndDate()
    {
        return $this->getData(self::SUBSCRIPTION_END_DATE);
    }

    /**
     * @inheritDoc
     * @since 200.7.0
     */
    public function setSubscriptionEndDate($subscriptionEndDate)
    {
        return $this->setData(self::SUBSCRIPTION_END_DATE, $subscriptionEndDate);
    }
}
