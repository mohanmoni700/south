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

use Magedelight\Subscribenow\Helper\Data as SubscriberHelper;
use Magedelight\Subscribenow\Model\Data\ProductSubscribers as ModelData;
use Magedelight\Subscribenow\Model\Service\EmailService;
use Magedelight\Subscribenow\Model\Service\EmailServiceFactory;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magedelight\Subscribenow\Model\System\Config\Backend\IntervalType;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Eav\Model\Entity\Increment\NumericValue;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class ProductSubscribers extends ModelData
{
    const EMAIL_DATE_FORMAT = 'MMMM dd, y';

    private $renewResetFields = [
        self::SUBSCRIPTION_ID,
        self::PROFILE_ID,
        self::TRIAL_COUNT,
        self::TOTAL_BILL_COUNT,
        self::NEXT_OCCURRENCE_DATE,
    ];

    private $subscriptionHistory;

    protected $subscribeHelper;

    protected $timezone;

    private $addressRepository;

    private $encryptor;

    private $emailService;

    private $addressRenderer;

    private $paymentHelper;

    private $numericValue;

    private $orderRepository;

    private $searchCriteriaBuilder;

    private $storeManager;

    private $localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        TimezoneInterface $timezone,
        ProductSubscriptionHistoryFactory $subscriptionHistory,
        SubscriberHelper $subscribeHelper,
        AddressRepositoryInterface $addressRepository,
        EncryptorInterface $encryptor,
        EmailServiceFactory $emailService,
        AddressRenderer $addressRender,
        PaymentHelper $paymentHelper,
        NumericValue $numericValue,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        Context $context,
        Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->subscriptionHistory = $subscriptionHistory;
        $this->subscribeHelper = $subscribeHelper;
        $this->timezone = $timezone;
        $this->addressRepository = $addressRepository;
        $this->encryptor = $encryptor;
        $this->emailService = $emailService;
        $this->addressRenderer = $addressRender;
        $this->paymentHelper = $paymentHelper;
        $this->numericValue = $numericValue;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->_scopeConfig = $_scopeConfig;
    }

    public function addHistory($updatedBy, $comment)
    {
        $this->subscriptionHistory->create()->addSubscriptionHistory(
            $this->getId(),
            $updatedBy,
            $comment
        );
    }

    /**
     * Skip subscription
     * Get next occurrence date of current subscription
     * @param int $subscriptionHistoryBy
     */
    public function skipSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CUSTOMER)
    {
        $nextOccurenceDate = date("Y-m-d", strtotime($this->getNextOccurrenceDate()));
        $nextCycle = $this->getNextSubscriptionDate();
        $this->setNextOccurrenceDate($nextCycle)->save();
        $storeNextDate = date("Y-m-d", strtotime($nextCycle));
        $comment = __("Subscription date skip to $storeNextDate from $nextOccurenceDate");

        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment, true);
    }

    /**
     * @param null $nextOccurenceDate
     * @return string
     */
    public function getNextSubscriptionDate($nextOccurenceDate = null)
    {
        if (!$nextOccurenceDate) {
            $nextOccurenceDate = $this->getNextOccurrenceDate();
        }
        if ($this->isTrialPeriod()) {
            $addTime = '+' . $this->getTrialPeriodFrequency() . array_search($this->getTrialPeriodUnit(), IntervalType::INTERVAL);
        } else {
            $addTime = '+' . $this->getBillingFrequency() . array_search($this->getBillingPeriod(), IntervalType::INTERVAL);
        }
        return date('Y-m-d H:i:s', strtotime($addTime, strtotime($nextOccurenceDate)));
    }

    public function subscriptionSucceed($message, $subscription = null, $modifiedBy = null)
    {
        if (!$subscription) {
            $subscription = $this;
        }

        if (!$modifiedBy) {
            $modifiedBy = $subscription->getModifiedBy();
        }

        $this->_eventManager->dispatch(
            'md_subscribenow_subscription_success',
            [
                'subscription' => $this,
                'message' => $message,
                'modified_by' => $modifiedBy
            ]
        );
    }

    public function afterSubscriptionCreate()
    {
        $comment = __('Subscription order #%1 created successfully', $this->getOrderIncrementId());
        $this->addHistory($this->getModifiedBy(), $comment);

        // Increase Successful Occurrence
        if ($this->isTrialPeriod()) {
            $this->setTrialCount($this->getTrialCount() + 1);
        } else {
            $this->setTotalBillCount($this->getTotalBillCount() + 1);
        }

        if ($this->getTrialPeriodMaxCycles() && $this->getTrialCount() >= $this->getTrialPeriodMaxCycles()) {
            $this->setIsTrial(0);
        }

        $this->setNextOccurrenceDate($this->getNextSubscriptionDate());

        $this->resetSuspensionThreshold();

        if ($this->getPeriodMaxCycles() && $this->getTotalBillCount() >= $this->getPeriodMaxCycles()) {
            $this->completeSubscription($this->getModifiedBy());
        }

        $this->subscriptionSucceed($comment);

        return $this;
    }

    public function completeSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CRON)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $this->setSubscriptionStatus(ProfileStatus::COMPLETED_STATUS)->save();
        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::COMPLETED_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function pauseSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CUSTOMER)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $this->setSubscriptionStatus(ProfileStatus::PAUSE_STATUS)->save();
        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::PAUSE_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function resumeSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CUSTOMER)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $startDate = $this->getSubscriptionStartDate();
        $currentNextDate = $this->getNextOccurrenceDate();
        $this->setSubscriptionStatus(ProfileStatus::ACTIVE_STATUS);

        /**  If subscription is not future subscription */
        if ($startDate != $currentNextDate) {
            $nextCycle = $this->getNextSubscriptionDate(date("Y-m-d H:i:s"));
            /** Next cycle should be updated with current date */
            if ($nextCycle > $this->getNextOccurrenceDate()) {
                $this->setNextOccurrenceDate($nextCycle);
            }
        }

        $this->save();

        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::ACTIVE_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function cancelSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CUSTOMER)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $this->setSubscriptionStatus(ProfileStatus::CANCELED_STATUS)->save();
        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::CANCELED_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function failedSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CRON)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::FAILED_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function suspendSubscription($subscriptionHistoryBy = ProductSubscriptionHistory::HISTORY_BY_CRON)
    {
        $currentStatus = $this->getSubscriptionStatus();
        $labels = $this->subscribeHelper->getStatusLabel();
        $comment = __("Change status %1 from %2", $labels[ProfileStatus::SUSPENDED_STATUS], $labels[$currentStatus]);
        $this->addHistory($subscriptionHistoryBy, $comment);
        $this->sendUpdateEmail($comment);
    }

    public function updateSubscription($postValue, $updateBy)
    {
        if (!empty($postValue['qty'])) {
            $this->updateSubscriptionQty($postValue['qty'], $updateBy);
        }
       
        if (!empty($postValue['subscription_start_date'])) {
            $this->updateNextOccurenceDate($postValue['subscription_start_date'], $updateBy);
        }
        $this->updateBillingFrequency($postValue, $updateBy);
        $this->updateSubscriptionAddress($postValue, $updateBy);
        $this->updatePaymentToken($postValue, $updateBy);
        $this->save();
    }

    public function updatePaymentToken($postValue, $updateBy)
    {
        if (!empty($postValue['md_savecard'])) {
            $originalToken = $this->encryptor->decrypt($this->getPaymentToken());
            $token = $this->encryptor->decrypt($postValue['md_savecard']);
            if ($originalToken != $token) {
                $this->setPaymentToken($postValue['md_savecard']);
                $comment = __('Card details updated');
                $this->addHistory($updateBy, $comment);
                $this->sendUpdateEmail($comment);
            }
        }
    }

    private function updateSubscriptionAddress($postValue, $updateBy)
    {
        //$billingEditable = $this->subscribeHelper->isBillingEditable();
        $billingEditable = (bool) $this->_scopeConfig->getValue('md_subscribenow/product_subscription/update_billing_address', ScopeInterface::SCOPE_STORE);
        $isBillingEdit = !$billingEditable && $updateBy == 2 ? false : true;

        if (!empty($postValue['md_billing_address']) && $isBillingEdit) {
            $originalBillingId = $this->getBillingAddressId();
            if ($originalBillingId != $postValue['md_billing_address']) {
                $this->setBillingAddressId($postValue['md_billing_address']);
                $comment = __("Billing Address Updated");
                $this->addHistory($updateBy, $comment);
            }
        }

        //$shippingEditable = $this->subscribeHelper->isShippingEditable();
        $shippingEditable = (bool) $this->_scopeConfig->getValue('md_subscribenow/product_subscription/update_shipping_address', ScopeInterface::SCOPE_STORE);
        $isShippingEdit = !$shippingEditable && $updateBy == 2 ? false : true;
        if (!empty($postValue['md_shipping_address']) && $isShippingEdit) {
            $originalShippingId = $this->getShippingAddressId();
            if ($originalShippingId != $postValue['md_shipping_address']) {
                $this->setShippingAddressId($postValue['md_shipping_address']);
                $comment = __("Shipping Address Updated");
                $this->addHistory($updateBy, $comment);
            }
        }
    }

    public function updateBillingFrequency($postValue, $updateBy)
    {
        if (!empty($postValue['md_billing_frequency'])) {
            $currentBillingCycle = $this->getBillingFrequencyCycle();
            $newBillingCycle = $postValue['md_billing_frequency'];

            if ($currentBillingCycle != $newBillingCycle) {
                $old = $this->getBillingPeriodLabel();
                $this->setBillingCycle($newBillingCycle);
                $new = $this->getBillingPeriodLabel();

                if ($this->subscribeHelper->isUpdateOccurrenceOnFrequency()) {
                    $nextCycle = $this->getNextSubscriptionDate(date("Y-m-d H:i:s"));
                    $this->setNextOccurrenceDate($nextCycle);
                }

                $comment = __('Billing Frequency Updated from %1 to %2', $old, $new);
                $this->addHistory($updateBy, $comment);
                $this->sendUpdateEmail($comment);
            }
        }
    }

    public function renewSubscription($updatedBy)
    {
        $newSubscription = clone $this;
        $this->resetProfileForRenew($newSubscription);

        $today = $this->timezone->date(null, null, false)->format('Y-m-d H:i:s');
        $newSubscription->setSubscriptionStartDate($today);
        $newSubscription->setNextOccurrenceDate($this->getNextSubscriptionDate($today));
        $newSubscription->setSubscriptionStatus(ProfileStatus::ACTIVE_STATUS);
        $newSubscription->save();

        $incrementInstance = $this->numericValue->setPrefix($newSubscription->getStoreId())
            ->setPadLength(8)->setPadChar('0');
        $newSubscription->setProfileId($incrementInstance->format($newSubscription->getId()))->save();

        $this->_eventManager->dispatch(
            'md_subscribenow_renew_subscription',
            ['old_subscription' => $this, 'new_subscription' => $newSubscription]
        );

        $this->sendRenewSubscriptionEmail($newSubscription);

        $this->subscriptionHistory->create()->addSubscriptionHistory(
            $newSubscription->getId(),
            $updatedBy,
            __("Subscription profile renewed from #%1", $this->getProfileId())
        );

        $comment = __("Subscription profile renewed to #%1", $newSubscription->getProfileId());
        $this->addHistory($updatedBy, $comment);
        return $newSubscription;
    }

    /**
     *
     * @param type $orderIncrementId
     * @return type
     */
    public function getOrderByIncrementId($orderIncrementId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId, 'eq')->create();
            return $this->orderRepository->getList($searchCriteria)->getFirstItem();
        } catch (Exception $ex) {
            return null;
        }
    }

    public function sendRenewSubscriptionEmail($subscription)
    {
        if (!$this->subscribeHelper->isSubscriptionRenewEmailSend($this->getStoreId())) {
            return false;
        }

        $order = $this->getOrderByIncrementId($subscription->getInitialOrderId());
        $orderItemInfo = $subscription->getOrderItemInfo();

        $item = null;
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getParentItemId() != null) {
                continue;
            }

            if ($subscription->getParentProductId()) {
                if ($subscription->getProductId() == $orderItem->getProductId()) {
                    $item = $orderItem;
                    break;
                }
            } else {
                if ($orderItemInfo['item'] == $orderItem->getProductId()) {
                    $item = $orderItem;
                    break;
                }
            }
        }

        if (!$item) {
            return false;
        }

        $nextDate = $this->timezone->date($subscription->getSubscriptionStartDate())->format('F d, Y');
        $billDate = $this->timezone->date($subscription->getNextOccurrenceDate())->format('F d, Y');
        $subscription->setSubscriptionStartDate($nextDate);
        $subscription->setNextOccurrenceDate($billDate);

        $emailVars = [
            'subscription' => $subscription,
            'order' => $order,
            'store' => $order->getStore(),
            'formatted_billing_address' => $subscription->getFormattedBillingAddress($order),
            'formatted_shipping_address' => $subscription->getFormattedShippingAddress($order),
            'payment_html' => $subscription->getPaymentHtml($order),
            'init_amount' => $order->formatPrice($subscription->getInitialAmount()),
            'trial_amount' => $order->formatPrice($subscription->getTrialBillingAmount()),
            'billing_amount' => $order->formatPrice($subscription->getBillingAmount()),
            'item_name' => $subscription->getProductName(),
            'item_sku' => $subscription->getProductSku(),
            'item_qty' => (float) $subscription->getQtySubscribed()
        ];

        $this->sendEmail($emailVars, EmailService::EMAIL_NEW_SUBSCRIPTION);
    }

    private function resetProfileForRenew($model)
    {
        foreach ($this->renewResetFields as $fields) {
            $model->unsetData($fields);
        }
        $this->setTrialCount(0);
        $this->setTotalBillCount(0);
        return $this;
    }

    private function updateNextOccurenceDate($date, $updateBy)
    {
        $originalDate = $this->timezone->date($this->getNextOccurrenceDate())->format('F d, Y');
        $utcDate = $this->getUtcDateTime($date);
        $newDate = $this->timezone->date($utcDate)->format('F d, Y');
        if ($newDate != $originalDate) {
            $this->setNextOccurrenceDate($utcDate);
            $comment = __("Changed next billing date $newDate from $originalDate");
            $this->addHistory($updateBy, $comment);
            $this->sendUpdateEmail($comment, true);
        }
    }

    public function updateSubscriptionQty($qty, $updateBy)
    {
        $qty = (float)$qty;
        $originalQty = (float)$this->getQtySubscribed();
        if ($qty != $originalQty) {
            if ($this->validateQty($qty)) {
                $this->setQtySubscribed($qty);
                $comment = __("Update product qty $qty from  $originalQty");
                $this->addHistory($updateBy, $comment);
                $this->sendUpdateEmail($comment);
            }
        }
    }

    /**
     * Validate Item Qty
     *
     * @param $itemQty
     * @return bool
     * @throws LocalizedException
     */
    public function validateQty($itemQty)
    {
        if ($itemQty) {
            $allowedQty = $this->subscribeHelper->getMaxAllowedQty();

            if ($allowedQty && $itemQty > $allowedQty) {
                $errorMessage = $this->subscribeHelper->getQtyErrorMessage();
                throw new LocalizedException($errorMessage);
            }
        }
        return true;
    }

    private function getUtcDateTime($date, $appendTime = true)
    {
        if ($appendTime) {
            $currentStoreTime = $this->timezone->date()->format(' H:i:s');
            $date .= $currentStoreTime;
        }
        return $this->timezone->convertConfigTimeToUtc($date);
    }

    public function sendUpdateEmail($comment, $isSkip = false)
    {
        if (!$this->subscribeHelper->isUpdateSubscriptionEmailSend($this->getStoreId())) {
            return false;
        }

        $store = $this->storeManager->getStore();

        $emailVars = [
            'update_message' => $comment,
            'subscriber_name' => $this->getSubscriberName(),
            'profile_id' => $this->getProfileId(),
            'created_at' => $this->getDateFormatted($this->getCreatedAt(), 2, $store, self::EMAIL_DATE_FORMAT),
            'store' => $store
        ];

        if ($isSkip) {
            $emailVars['next_date'] = $this->getDateFormatted($this->getNextOccurrenceDate(), 2, $store, self::EMAIL_DATE_FORMAT);
        }

        $this->sendEmail($emailVars, EmailService::EMAIL_PROFILE_UPADATE);
    }

    public function sendEmail($emailVariable, $type)
    {
        $emailService = $this->emailService->create();
        $emailService->setStoreId($this->getStoreId());
        $emailService->setTemplateVars($emailVariable);
        $emailService->setType($type);
        $email = $this->getSubscriberEmail();
        $emailService->setSendTo($email);
        $emailService->send();
    }

    public function updateSubscriptionFailedCount()
    {
        $failedCount = $this->getSuspensionThreshold() + 1;
        $this->setSuspensionThreshold($failedCount)->save();
    }

    /**
     * @param Order $order
     * @return string|null
     */
    public function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     * @return string|null
     */
    public function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    public function getPaymentHtml($order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStore()->getStoreId()
        );
    }

    /**
     * Checks weather subscription is on trial
     * @return bool
     */
    public function isTrialPeriod()
    {
        return $this->getTrialPeriodMaxCycle() != $this->getTrialCount();
    }

    public function getDateFormatted($date, $format, $store, $pattern = null)
    {
        return $this->timezone->formatDateTime(
            new \DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $store),
            $pattern
        );
    }

    public function setBillingCycle($key)
    {
        $intervals = $this->subscribeHelper->getSubscriptionInterval();
        if ($intervals && !isset($intervals[$key])) {
            $intervals = $this->subscribeHelper->prepareBillingInterval($this);
        }

        $interval = $intervals[$key];
        if ($interval) {
            $this->setBillingFrequencyCycle($key);
            $this->setBillingPeriodLabel($interval['interval_label']);
            $this->setBillingFrequency($interval['no_of_interval']);
            $this->setBillingPeriod(IntervalType::INTERVAL[$interval['interval_type']]);
        }
    }

    /**
     * Reset Failed Threshold of Subscription Profile
     */
    public function resetSuspensionThreshold()
    {
        if ($this->getSuspensionThreshold()) {
            $this->setSuspensionThreshold(0);
        }
    }
}
