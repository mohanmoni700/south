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

use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magedelight\Subscribenow\Model\System\Config\Backend\IntervalType;
use Magedelight\Subscribenow\Model\System\Config\Backend\PaymentMethod;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * General Configuration
     */
    const XML_PATH_SUBSCRIBENOW_ACTIVE = 'md_subscribenow/general/enabled';
    const XML_PATH_SUBSCRIPTION_INTERVAL = 'md_subscribenow/general/manage_subscription_interval';
    const XML_PATH_SUBSCRIPTION_LABEL1 = 'md_subscribenow/subscription_label/label1';
    const XML_PATH_SUBSCRIPTION_LABEL2 = 'md_subscribenow/subscription_label/label2';
    const XML_PATH_ALLOWED_PAYMENT_METHODS = 'md_subscribenow/payment/payment_gateway';
    const XML_PATH_ALLOWED_SHIPPING_METHODS = 'md_subscribenow/shipping/shipping_method';
    const XML_PATH_SUBSCRIBENOW_PRODUCT_MAX_QTY = 'md_subscribenow/product_subscription/maximum_quantity_subscribe';
    const XML_PATH_UPDATE_PROFILE_DAY_LIMIT = 'md_subscribenow/product_subscription/update_profile_before';
    const XML_PATH_CAN_CANCEL_SUBSCRIPTION = 'md_subscribenow/product_subscription/allow_cancel_subscription';
    const XML_PATH_CAN_SKIP_SUBSCRIPTION = 'md_subscribenow/product_subscription/allow_skip_subscription';
    const XML_PATH_CAN_PAUSE_SUBSCRIPTION = 'md_subscribenow/product_subscription/allow_pause_subscription';
    const XML_PATH_SUBSCRIPTION_PRODUCT_LIST_TEXT = 'md_subscribenow/subscription_label/subscription_list_text';
    const XML_PATH_SUBSCRIPTION_SUMMARY_ENABLED = 'md_subscribenow/subscription_label/enabled';
    const XML_PATH_SUBSCRIPTION_SUMMARY_HEADER = 'md_subscribenow/subscription_label/header_summary_text';
    const XML_PATH_SUBSCRIPTION_SUMMARY_CONTENT = 'md_subscribenow/subscription_label/content_summary_text';
    const XML_PATH_SUBSCRIPTION_FREE_SHIPPING = 'md_subscribenow/shipping/free_shipping_subscription';
    const XML_PATH_DYNAMIC_PRICE = 'md_subscribenow/general/dynamic_price';
    const XML_PATH_ALLOWED_BILLING_EDIT = 'md_subscribenow/product_subscription/update_billing_address';
    const XML_PATH_ALLOWED_SHIPPING_EDIT = 'md_subscribenow/product_subscription/update_shipping_address';
    const XML_PATH_CUSTOMER_ADDRESS_TEMPLATE = 'customer/address_templates/html';
    const XML_PATH_CAN_UPDATE_BILLING_FREQUENCY = 'md_subscribenow/product_subscription/update_billing_frequency';
    const XML_PATH_CAN_UPDATE_NEXT_OCCURRENCE_FROM_FREQUENCY = 'product_subscription/update_next_occurrence_on_frequency';
    const XML_PATH_SELECT_AUTO_SHIPPING = 'shipping/select_auto_shipping';
    const XML_PATH_CUSTOMER_GROUP_SUBSCRIPTION  = 'md_subscribenow/general/subscriber_customer_group';
    const XML_PATH_CUSTOMER_GROUP_SUBSCRIPTION_MSG  = 'md_subscribenow/general/subscription_customer_group_message';
    const XML_PATH_CUSTOMER_GROUP_VIEW_SUBSCRIPTION = 'md_subscribenow/general/allow_to_view_subscription';
    const XML_PATH_CUSTOMER_GROUP_AUTOREGISTER_SUBSCRIPTION = 'md_subscribenow/general/autoregistor_guest_subscription';
    const XML_PATH_CUSTOMER_GROUP_ADDTOCART_SUBSCRIPTION = 'md_subscribenow/general/allow_to_addtocart_subscription';
    /**
     * Email Configuration.
     */
    const XML_PATH_SUBSCRIPTION_SENDER = 'email/subscription_email_sender';
    const XML_PATH_NEW_SUBSCRIPTION_EMAIL_SEND = 'email/allow_new_subscription_mail';
    const XML_PATH_SUBSCRIPTION_EMAIL = 'email/new_subscription_template';
    const XML_PATH_SUBSCRIPTION_EMAIL_BCC = 'email/new_subscription_copyto';
    const XML_PATH_UPDATE_SUBSCRIPTION_EMAIL_SEND = 'email/allow_update_subscription_mail';
    const XML_PATH_PROFILE_UPDATE_EMAIL = 'email/subscription_update_template';
    const XML_PATH_PROFILE_UPDATE_EMAIL_BCC = 'email/subscription_update_copyto';
    const XML_PATH_PAYMENT_FAILED_EMAIL_SEND = 'email/allow_payment_failed_mail';
    const XML_PATH_PAYMENT_FAILED_EMAIL = 'email/payment_fail_template';
    const XML_PATH_PAYMENT_FAILED_EMAIL_BCC = 'email/payment_fail_template_copyto';
    const XML_PATH_SUBSCRIPTION_REMINDER_EMAIL_SEND = 'email/allow_subscription_reminder_mail';
    const XML_PATH_REMINDER_EMAIL = 'email/subscription_reminder_template';
    const XML_PATH_REMINDER_EMAIL_BCC = 'email/subscription_reminder_template_copyto';
    const XML_PATH_EWALLET_TOPUP_REMINDER_EMAIL_SEND = 'email/allow_ewallet_topup_reminder_mail';
    const XML_PATH_EWALLET_TOPUP_REMINDER_EMAIL = 'email/subscription_ewallet_topup_reminder_template';
    const XML_PATH_EWALLET_TOPUP_REMINDER_EMAIL_BCC = 'email/subscription_ewallet_reminder_template_copyto';
    const XML_PATH_SUBSCRIPTION_RENEW_EMAIL_SEND = 'email/allow_subscription_renew_mail';
    const XML_PATH_SUBSCRIPTION_ORDER_EMAIL_SEND = 'email/allow_subscription_order_mail';

    /**
     * Period units.
     * @var string
     */
    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_YEAR = 'year';

    /**
     * @var Json
     */
    private $serialize;
    /**
     * @var PaymentHelper
     */
    private $paymentHelper;
    /**
     * @var PaymentMethod
     */
    private $paymentMethod;
    /**
     * @var CheckoutSessionFactory
     */
    private $checkoutSessionFactory;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var Array
     */
    private $paymentMethodsArray = [];
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        Json $serializer,
        PaymentHelper $paymentHelper,
        PaymentMethod $paymentMethod,
        Session $customerSession,
        CheckoutSessionFactory $checkoutSessionFactory,
        DataObjectFactory $dataObject,
        \Magento\Framework\App\State $_state,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->serialize = $serializer;
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethod = $paymentMethod;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->dataObjectFactory = $dataObject;
        $this->state = $_state;
        $this->_resource = $resource;
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIBENOW_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    public function firstSubscriptionLabel()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_LABEL1, ScopeInterface::SCOPE_STORE);
    }

    public function secondSubscriptionLabel()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_LABEL2, ScopeInterface::SCOPE_STORE);
    }

    public function getSubscriptionInterval($toArray = true, $field = null)
    {
        $interval = $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_INTERVAL, ScopeInterface::SCOPE_STORE);
        if ($interval && ($toArray || $field)) {
            $interval = $this->serialize->unserialize($interval);
            if ($field) {
                return array_combine(array_keys($interval), array_column($interval, $field));
            }
        }

        return $interval;
    }

    public function getAllowedCustomerGroups()
    {
        $allowedCustomerGroups = $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_GROUP_SUBSCRIPTION, ScopeInterface::SCOPE_WEBSITE);
        $allowedCustomerGroupsIds = [];
        if ($allowedCustomerGroups) {
            $allowedCustomerGroupsIds = explode(',', $allowedCustomerGroups);
        }
        return $allowedCustomerGroupsIds;
    }


    public function isAllowToViewPlan()
    {
        $isAllow = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_GROUP_VIEW_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $isAllow;
    }

    public function isAllowToAutoRegister()
    {
        $isAutoRegister = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_GROUP_AUTOREGISTER_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $isAutoRegister;
    }

    public function isAllowToAddtoCart()
    {
        $isAllow = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_GROUP_ADDTOCART_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $isAllow;
    }

    public function getNotAllowedCustomerMessage()
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_GROUP_SUBSCRIPTION_MSG,
            ScopeInterface::SCOPE_WEBSITE
        );
        return $message;
    }

    public function getAllowedPaymentMethods()
    {
        $methods = [];
        $allowedMethods = $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_PAYMENT_METHODS,
            ScopeInterface::SCOPE_STORE
        );
        $methodList = ($allowedMethods) ? explode(',', $allowedMethods) : [];
        // array_push($methodList, 'free');

        if ($methodList) {
            foreach ($methodList as $method) {
                $paymentCodeCCVault = str_replace('_cc_vault', '', $method);

                // If method doesn't include cc like braintree_paypal_vault
                $methods[] = str_replace('_vault', '', $paymentCodeCCVault);
            }
        }

        return $methods;
    }

    /**
     * @return mixed
     */
    public function useDynamicPrice()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DYNAMIC_PRICE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getUpdateProfileDayLimit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_UPDATE_PROFILE_DAY_LIMIT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function canCancelSubscription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CAN_CANCEL_SUBSCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function canSkipSubscription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CAN_SKIP_SUBSCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function canPauseSubscription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CAN_PAUSE_SUBSCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return boolean
     */
    public function isShowCartSummaryBlock()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_SUMMARY_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getSummaryBlockTitle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_SUMMARY_HEADER, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getSummaryBlockContetnt()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_SUMMARY_CONTENT, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string
     */
    public function getListPageText()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_PRODUCT_LIST_TEXT, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return boolean
     */
    public function getCustomerAddressTemplate()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_ADDRESS_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return subscription status array
     *
     * @return array
     */
    public function getStatusLabel()
    {
        return [
            'unknown' => 'Unknown',
            '0' => __('Pending'),
            '1' => __('Active'),
            '2' => __('Paused'),
            '3' => __('Expired'),
            '4' => __('Cancelled'),
            '5' => __('Suspended'),
            '6' => __('Failed'),
            '7' => __('Complete'),
            '8' => __('Renew'),
        ];
    }

    /**
     * @param type $item
     * @param type $t
     *
     * @return type
     */
    public function getCustomOptionPrice($item, $t)
    {
        $CustomOptionprice = 0;

        $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

        if (isset($productOptions['options'])) {
            foreach ($productOptions['options'] as $key => $value) {
                $optionType = $value['option_type'];
                if ($optionType == 'drop_down' || $optionType == 'multiple' || $optionType == 'radio' || $optionType == 'checkbox' || $optionType == 'multiple') {
                    $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
                            ->get('Magento\Framework\App\ResourceConnection');
                    $connection = $this->_resources->getConnection();
                    $optionValue = explode(',', $value['option_value']);
                    for ($count = 0; $count < count($optionValue); ++$count) {
                        $select = $connection->select()
                                ->from(
                                    ['mdsub' => $this->_resources->getTableName('catalog_product_option_type_price')]
                                )
                                ->where('mdsub.option_type_id=?', $optionValue[$count]);
                        $result = $connection->fetchAll($select);
                        if (isset($result)) {
                            if ($result[0]['price_type'] == 'fixed') {
                                $CustomOptionprice += $result[0]['price'];
                            } else {
                                $CustomOptionprice += ($t * ($result[0]['price'] / 100));
                            }
                        }
                    }
                } else {
                    $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
                            ->get('Magento\Framework\App\ResourceConnection');
                    $connection = $this->_resources->getConnection();

                    $select = $connection->select()
                            ->from(
                                ['mdsub' => $this->_resources->getTableName('catalog_product_option_price')]
                            )
                            ->where('mdsub.option_id=?', $value['option_id']);

                    $result = $connection->fetchAll($select);
                    if (isset($result)) {
                        if ($result[0]['price_type'] == 'fixed') {
                            $CustomOptionprice += $result[0]['price'];
                        } else {
                            $CustomOptionprice += ($t * ($result[0]['price'] / 100));
                        }
                    }
                }
            }
        }

        return $CustomOptionprice;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE ? true : false;
    }

    /**
     * Get Initial Amount Title
     * @return string
     */
    public function getInitAmountTitle()
    {
        return __('Initial Fee');
    }

    /**
     * Get Trial Amount Title
     * @return string
     */
    public function getTrialAmountTitle()
    {
        return __('Trial Billing Amount');
    }

    /**
     * Get Billing Period Title
     * @return string
     */
    public function getBillingPeriodTitle()
    {
        return __('Billing Period');
    }

    /**
     * Get Billing Cycle Title
     * @return string
     */
    public function getBillingCycleTitle()
    {
        return __('Billing Cycle');
    }

    /**
     * Get Trial Period Title
     * @return string
     */
    public function getTrialPeriodTitle()
    {
        return __('Trial Period');
    }

    /**
     * Get Trial Cycle Title
     * @return string
     */
    public function getTrialCycleTitle()
    {
        return __('Trial Cycle');
    }

    /**
     * Get Subscription Start Date Title
     * @return string
     */
    public function getSubscriptionStartDateTitle()
    {
        return __('Subscription Start Date');
    }

    /**
     * Get Subscription End Date Title
     * @return string
     * @since 200.7.0
     */
    public function getSubscriptionEndDateTitle()
    {
        return __('Subscription End Date');
    }

    /**
     * Get Allowed Maximum Quantity To Subscribe Per Product
     * @return int
     */
    public function getMaxAllowedQty()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBSCRIBENOW_PRODUCT_MAX_QTY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get Max Quantity Error Message
     * @return string
     */
    public function getQtyErrorMessage()
    {
        return __('Subscription Product quantity should be %1 or less.', $this->getMaxAllowedQty());
    }

    /**
     * Get Interval Label
     * @return string|null
     */
    public function getIntervalLabel($key)
    {
        $interval = $this->getSubscriptionInterval();

        if (!empty($interval) && array_key_exists($key, $interval)) {
            $result = $interval[$key];
        } elseif (!empty($interval)) {
            $result = reset($interval);
        }

        if ($result && $result['interval_label']) {
            return $result['interval_label'];
        }

        return null;
    }

    public function getScopeValue($scopePath, $storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'md_subscribenow/' . $scopePath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSubscriptionListingText($product)
    {
        $html = null;
        $customerGroup = $this->customerSession->getCustomerGroupId();
        $allowedCustomerGroupIds = $this->getAllowedCustomerGroups();
        if (!in_array($customerGroup, $allowedCustomerGroupIds)) {
            return $html;
        }
        if ($this->isModuleEnable() &&
            $product instanceof \Magento\Catalog\Model\Product &&
            $product->getIsSubscription()
        ) {
            $text = $this->getListPageText();
            if ($text) {
                $url = $product->getUrlInStore() . "#md_subscription_content";
                $html = __('<span class="subscription_product_text"><a href="%1">%2</a></span>', $url, $text);
            }
        }

        return $html;
    }

    public function getMaxFailedAllowedTimes($storeId = 0)
    {
        return $this->getScopeValue('general/maximum_payment_failed', $storeId);
    }

    public function getEWalletPaymentTitle($storeId = 0)
    {
        $walletTitle = $this->scopeConfig->getValue('md_wallet/general/ewallet_title');
        if (!$walletTitle) {
            $walletTitle = "Magedelight EWallet";
        }
        return $walletTitle;
    }

    public function getPaymentTitle($code = null)
    {
        if ($code) {
            if ($code == 'magedelight_ewallet') {
                return $this->getEWalletPaymentTitle();
            }
            try {
                $title = $this->paymentHelper->getMethodInstance($code)->getTitle();
            } catch (\Exception $ex) {
                $title = $this->getPaymentMethodTitle($code);
            }
        }
        return $title;
    }

    public function getPaymentMethodTitle($code)
    {
        if (!$this->paymentMethodsArray) {
            $paymentMethods = $this->paymentMethod->toOptionArray();
            $methods = [];
            foreach ($paymentMethods as $method) {
                $methods[$method['value']] = $method['label'];
            }
            $this->paymentMethodsArray = $methods;
        }

        return isset($this->paymentMethodsArray[$code])
                ? $this->paymentMethodsArray[$code]->getText() : null;
    }

    public function getCurrentQuote($quote = null)
    {
        if ($quote) {
            return $quote;
        }

        $quote = $this->checkoutSessionFactory->create()->getQuote();

        return $quote;
    }

    public function hasSubscriptionProduct($quote = null)
    {
        $result = false;
        if ($this->isModuleEnable()) {
            $quote = $this->getCurrentQuote($quote);
            $allItems = $quote->getAllItems();

            if (!count($allItems)) {
                return $result;
            }

            foreach ($allItems as $item) {
                $buyRequest = $item->getBuyRequest()->getData();
                if (!empty($buyRequest)
                    && isset($buyRequest['options']['_1'])
                    && ($buyRequest['options']['_1'] == 'subscription')
                ) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    private function unsetOrderItemData(&$orderItemInfo)
    {
        if (!$orderItemInfo) {
            return false;
        }

        unset(
            $orderItemInfo['form_key'],
            $orderItemInfo['related_product'],
            $orderItemInfo['subscription_start_date'],
            $orderItemInfo['related_product'],
            $orderItemInfo['uenc'],
            $orderItemInfo['selected_configurable_option'],
            $orderItemInfo['instant_purchase_payment_token'],
            $orderItemInfo['instant_purchase_shipping_address'],
            $orderItemInfo['instant_purchase_billing_address'],
            $orderItemInfo['instant_purchase_carrier'],
            $orderItemInfo['instant_purchase_shipping'],
            $orderItemInfo['original_qty']
        );
    }

    /**
     * @param array $array
     * @return int[]
     */
    private function multiToFlatArray(array $array)
    {
        $flatArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flatArray = array_merge($flatArray, $this->multiToFlatArray($value));
            } else {
                $flatArray[$key] = $value;
            }
        }

        return $flatArray;
    }

    /**
     * @param null $key
     * @param null $value
     * @return array|object
     */
    private function createNewDataObject($key = null, $value = null)
    {
        if ($key && $value) {
            return $this->dataObjectFactory->create()->setData([$key => $value]);
        }
        return [];
    }

    public function setBuyRequest(&$product, $orderItemInfo)
    {
        if (!$this->useDynamicPrice()) {
            return false;
        }

        $this->unsetOrderItemData($orderItemInfo);

        $options = ['info_buyRequest' => $this->createNewDataObject('value', $this->serialize->serialize($orderItemInfo))];
        $superAttributes = !empty($orderItemInfo['super_attribute']) ? $orderItemInfo['super_attribute'] : null;
        if ($superAttributes) {
            $options['attributes'] = $this->serialize->serialize($superAttributes);
        }

        if ($product->getTypeId() == 'configurable' && $superAttributes) {
            $child = $product->getTypeInstance()->getProductByAttributes($superAttributes, $product);
            $options['simple_product'] = $this->createNewDataObject('product', $child);
        }

        if ($product->getTypeId() == 'bundle') {
            $bundleSelectedOption = [];
            $bundleOption = $orderItemInfo['bundle_option'];
            $bundleOptionQty = $orderItemInfo['bundle_option_qty'];

            if ($bundleOption) {
                $bundleSelectedOption = $this->multiToFlatArray($bundleOption);
            }

            if ($bundleSelectedOption) {
                foreach ($bundleSelectedOption as $bundleOptionId) {
                    $qty = $bundleOptionQty && !empty($bundleOptionQty[$bundleOptionId])
                        ? $bundleOptionQty[$bundleOptionId] : 1;

                    $options['selection_qty_' . $bundleOptionId] = $this->createNewDataObject('value', $qty);
                }

                $options['bundle_selection_ids'] = $this->createNewDataObject('value', $this->serialize->serialize($bundleOption));
            }
        }

        $product->setCustomOptions($options);
        $customOptions = $orderItemInfo['options'];
        if ($customOptions) {
            unset($customOptions['_1']);

            $optionIds = array_keys($customOptions);
            $product->addCustomOption('option_ids', implode(',', $optionIds));
            foreach ($customOptions as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $optionValue = implode(',', $optionValue);
                }
                $product->addCustomOption('option_' . $optionId, $optionValue);
            }
        }
    }

    /**
     * @return bool
     */
    public function isBillingEditable($storeId = 0)
    {
        return (bool) $this->getScopeValue(
            self::XML_PATH_ALLOWED_BILLING_EDIT,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isShippingEditable($storeId = 0)
    {
        return (bool) $this->getScopeValue(
            self::XML_PATH_ALLOWED_SHIPPING_EDIT,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isProfileEditable($status, $nextOccurrence = null)
    {
        $restrictStatus = [
            ProfileStatus::COMPLETED_STATUS,
            ProfileStatus::FAILED_STATUS,
            ProfileStatus::CANCELED_STATUS,
            ProfileStatus::EXPIRED_STATUS,
            ProfileStatus::SUSPENDED_STATUS,
        ];
        if (in_array($status, $restrictStatus)) {
            return false;
        }

        $limit = $this->getUpdateProfileDayLimit();
        if ($nextOccurrence && $limit) {
            $nextOccurrencesDay = date("Y-m-d", strtotime($nextOccurrence));
            $today = date("Y-m-d", time());
            $daysLength = 60 * 60 * 24;

            $nextOccurrencesTime = strtotime($nextOccurrencesDay);
            $currentTime = strtotime($today);
            $dateDifference = abs($currentTime - $nextOccurrencesTime) / $daysLength;
            return $limit < $dateDifference;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function isNewSubscriptionEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_NEW_SUBSCRIPTION_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isUpdateSubscriptionEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_UPDATE_SUBSCRIPTION_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isPaymentFailedEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_PAYMENT_FAILED_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isSubscriptionReminderEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_SUBSCRIPTION_REMINDER_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isEwalletTopupReminderEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_EWALLET_TOPUP_REMINDER_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isSubscriptionRenewEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_SUBSCRIPTION_RENEW_EMAIL_SEND, $storeId);
    }

    /**
     * @return mixed
     */
    public function isSubscriptionOrderEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_SUBSCRIPTION_ORDER_EMAIL_SEND, $storeId);
    }

    /**
     * Give user to update billing period (Daily/Weekly/Monthly)
     * @return boolean
     */
    public function canUpdateBillingFrequency($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_CAN_UPDATE_BILLING_FREQUENCY, $storeId);
    }

    public function isUpdateOccurrenceOnFrequency($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_CAN_UPDATE_NEXT_OCCURRENCE_FROM_FREQUENCY, $storeId);
    }

    public function prepareBillingInterval($subscription)
    {
        $interval = [];
        if ($subscription) {
            $intervalType = IntervalType::INTERVAL;
            $interval = [
                'interval_type' => array_search($subscription->getBillingPeriod(), $intervalType),
                'no_of_interval' => $subscription->getBillingFrequency(),
                'interval_label' => $subscription->getBillingPeriodLabel(),
                'is_selected' => 1
            ];
        }
        return $interval;
    }

    /**
     * @param string $key
     * @param object $subscription
     * @return array
     */
    public function getBillingInterval($key, $subscription)
    {
        $intervals = [];
        if (!$subscription) {
            return $intervals;
        }

        $intervalFound = 0;
        $configIntervals = $this->getSubscriptionInterval();
        foreach ($configIntervals as $intervalKey => $value) {
            $intervals[$intervalKey] = $value;
            $intervals[$intervalKey]['is_selected'] = ($key == $intervalKey) ? 1 : 0;
            if ($intervals[$intervalKey]['is_selected']) {
                $intervalFound = 1;
            }
        }

        if (!$intervalFound) {
            $intervals[$key] = $this->prepareBillingInterval($subscription);
        }

        return $intervals;
    }

    public function isAutoSelectShippingMethod($storeId = 0)
    {
        return (bool) $this->getScopeValue(
            self::XML_PATH_SELECT_AUTO_SHIPPING,
            $storeId
        );
    }

    public function getNACardInfoMethods()
    {
        return [
            'magedelight_ewallet',
            'cashondelivery',
            'braintree_paypal'
        ];
    }
}
