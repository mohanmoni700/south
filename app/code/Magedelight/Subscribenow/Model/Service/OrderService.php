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

use Magedelight\Subscribenow\Helper\Data as subscriptionHelper;
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory as SubscriptionFactory;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;
use Magedelight\Subscribenow\Model\Service\Order\Generate;
use Magento\Eav\Model\Entity\Increment\NumericValue;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderService
{
    private $subscriptionHelper;

    private $subscriptionFactory;

    private $subscriptionService;

    private $numericValue;

    /**
     * @var \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    private $subscriptionModel;
    /**
     * @var PaymentService
     */
    private $paymentService;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var array
     */
    public $intervalType = [
        'day' => 1,
        'week' => 2,
        'month' => 3,
        'year' => 4,
    ];
    /**
     * @var Generate
     */
    private $generate;

    protected $additionalInfoData = [];

    /**
     * OrderService constructor.
     * @param subscriptionHelper $subscriptionHelper
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionService $subscriptionService
     * @param NumericValue $numericValue
     * @param PaymentService $paymentService
     * @param TimezoneInterface $timezone
     * @param Generate $generate
     */
    public function __construct(
        subscriptionHelper $subscriptionHelper,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionService $subscriptionService,
        NumericValue $numericValue,
        PaymentService $paymentService,
        TimezoneInterface $timezone,
        Generate $generate,
        Logger $logger,
        EventManager $eventManager,
        Json $serializer,
        ResolverInterface $localeResolver
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionService = $subscriptionService;
        $this->numericValue = $numericValue;
        $this->paymentService = $paymentService;
        $this->timezone = $timezone;
        $this->generate = $generate;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->serializer = $serializer;
        $this->localeResolver = $localeResolver;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    private function initSubscriptionModel()
    {
        $this->subscriptionModel = $this->subscriptionFactory->create();
        return $this;
    }

    public function getSubscriptionService($product, $request)
    {
        return $this->subscriptionService->getProductSubscriptionDetails($product, $request);
    }

    public function logInfo($message = null)
    {
        if ($message) {
            $this->logger->info($message);
        }
    }

    public function createSubscriptionOrder($subscription, $modifiedBy = ProductSubscriptionHistory::HISTORY_BY_ADMIN)
    {
        $subscription->setModifiedBy($modifiedBy);
        try {
            $this->logInfo("Process start for subscription profile # " . $subscription->getProfileId());
            $this->generate->setProfile($subscription)->generateOrder();
            $this->logInfo("Process successfully end for subscription profile # " . $subscription->getProfileId());
            return true;
        } catch (LocalizedException $ex) {
            $this->orderFailed($subscription, $modifiedBy, $ex->getMessage(), null);
            throw new \Exception($ex->getMessage());
        } catch (\Exception $ex) {
            $message = null;
            if (!$ex->getMessage()) {
                $trace = $ex->getTrace()[0];
                $error = $trace['class'] . $trace['type'] . $trace['function'];
                if ($error == 'Braintree\Util::throwStatusCodeException') {
                    $message = __("Card is missing or not activated for the subscription profile");
                } else {
                    $message = $ex->getTraceAsString();
                }
            }
            $this->orderFailed($subscription, $modifiedBy, $message, $ex);

            throw new \Exception(__("There was an error when generating subscription order #%1", $subscription->getProfileId()));
        }
    }

    private function orderFailed($subscription, $modifiedBy, $message = null, $exeception = null)
    {
        $this->eventManager->dispatch(
            'md_subscribenow_subscription_failed',
            [
                'subscription' => $subscription,
                'error_message' => $message,
                'exception' => $exeception,
                'modified_by' => $modifiedBy
            ]
        );
    }

    /**
     * @param $order
     * @param $item
     * @return boolean
     */
    public function isValidOrderRequest($order, $item)
    {
        $infoBuyRequest = $item->getBuyRequest()->getData();
        if (!$this->subscriptionService->checkProductRequest($infoBuyRequest)) {
            $failedMessage = __(
                "Subscription creation failed of order #%1, 
                Error : Subscription option not set in Product #%2",
                $order->getIncrementId(),
                $item->getProduct()->getId()
            );

            $this->logInfo($failedMessage);
            return false;
        }
        return true;
    }

    /**
     * @param $order
     * @param $item
     * @return null
     * @throws \Exception|LocalizedException
     */
    public function createSubscriptionProfile($order, $item)
    {
        if (!$this->isValidOrderRequest($order, $item)) {
            return false;
        }

        $this->initSubscriptionModel()
            ->setOrderInfo($order, $item)
            ->setItemInfo($order, $item)
            ->setPaymentInfo($order)
            ->setShippingInfo($order)
            ->setAdditionalInfo($order, $item);

        $this->getSubscriptionModel()->save();
        $this->getSubscriptionModel()->setProfileId($this->getSubscriptionIncrementId());
        if ($this->getSubscriptionModel()->getPeriodMaxCycles()
            && $this->getSubscriptionModel()->getTotalBillCount() >= $this->getSubscriptionModel()->getPeriodMaxCycles()
        ) {
            $this->getSubscriptionModel()->completeSubscription(
                ProductSubscriptionHistory::HISTORY_BY_CUSTOMER
            );
        }
        $this->getSubscriptionModel()->save();

        $this->sendEmail($order, $item);
    }

    private function setAdditionalInfo($order, $item)
    {
        $this->getSubscriptionModel()
            ->setNextOccurrenceDate($this->getNextOccurenceDate())
            ->setlastBillDate($this->timezone->convertConfigTimeToUtc($this->timezone->date()->format('Y-m-d H:i:s')));
        if (!$this->isFutureOccurence()) {
            if ($this->getSubscriptionModel()->getIsTrial()) {
                $this->getSubscriptionModel()->setTrialCount(1);
            } else {
                $this->getSubscriptionModel()->setTotalBillCount(1);
            }
        }

        $subscriptionStartDate = $this->getSubscriptionModel()->getSubscriptionStartDate();
        $this->getSubscriptionModel()->setSubscriptionStartDate(
            $this->getUtcDateTime($subscriptionStartDate)
        );

        $this->setItemProductOption($order, $item);

        if ($this->additionalInfoData) {
            $this->getSubscriptionModel()->setAdditionalInfo($this->additionalInfoData);
        }
    }

    public function setItemProductOption($order, $item)
    {
        $itemProductOptions = [];
        if ($item->getProductType() == 'bundle') {
            $bundleItemProductOptions = [];
            $product = $item->getProduct();
            $customOption = $product->getCustomOption('bundle_option_ids');
            $optionIds = $this->serializer->unserialize($customOption->getValue());
            $options = $product->getTypeInstance(true)->getOptionsByIds($optionIds, $product);
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = $this->serializer->unserialize($customOption->getValue());
            $selections = $product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $product);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($selectionQty) {
                        $option = $options->getItemById($selection->getOptionId());
                        if (!isset($itemProductOptions[$option->getId()])) {
                            $bundleItemProductOptions[$option->getId()] = [
                                'option_id' => $option->getId(),
                                'label' => $option->getTitle(),
                                'value' => [],
                            ];
                        }

                        $bundleItemProductOptions[$option->getId()]['value'][] = [
                            'title' => $selection->getName(),
                            'qty' => $selectionQty->getValue(),
                            'price' => $this->calculateDiscount($product, $selection),
                        ];
                    }
                }
            }

            if ($bundleItemProductOptions) {
                $itemProductOptions['bundle_options'] = $bundleItemProductOptions;
            }
        } else {
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getQuoteItemId() == $item->getId()) {
                    $productOptions = $orderItem->getProductOptions();
                    if ($productOptions) {
                        unset($productOptions['info_buyRequest']);
                        $itemProductOptions = $productOptions;
                    }

                    break;
                }
            }
        }
        $this->additionalInfoData['product_options'] = $itemProductOptions;
        return $itemProductOptions;
    }

    private function getNextOccurenceDate()
    {
        $subscription = $this->getSubscriptionModel();
        $currentTime = $this->timezone->date()->format(' H:i:s');
        $startDate = $subscription->getSubscriptionStartDate() . $currentTime;
        if ($this->isFutureOccurence()) {
            return $this->timezone->convertConfigTimeToUtc($startDate);
        }
        if ($subscription->getIsTrial()) {
            $addTime = '+' . $subscription->getTrialPeriodFrequency() . array_search($subscription->getTrialPeriodUnit(), $this->intervalType);
        } else {
            $addTime = '+' . $subscription->getBillingFrequency() . array_search($subscription->getBillingPeriod(), $this->intervalType);
        }
        $nextCycle = date('Y-m-d H:i:s', strtotime($addTime, strtotime($startDate)));
        return $this->timezone->convertConfigTimeToUtc($nextCycle);
    }

    /**
     * @return boolean
     */
    private function isFutureOccurence()
    {
        $startDate = $this->getSubscriptionModel()->getSubscriptionStartDate();
        $today = $this->timezone->date()->format('Y-m-d');
        return $startDate > $today;
    }

    private function setShippingInfo($order)
    {
        if (!$order->getIsVirtual()) {
            $this->additionalInfoData['shipping_title'] = $order->getShippingDescription();
            $this->getSubscriptionModel()->setShippingMethodCode($order->getShippingMethod());
        }
        return $this;
    }

    private function setPaymentInfo($order)
    {
        $paymentService = $this->paymentService->get($order);
        $this->getSubscriptionModel()->setPaymentToken($paymentService->getPaymentToken());
        $this->getSubscriptionModel()->setPaymentMethodCode($paymentService->getMethodCode());
        $this->getSubscriptionModel()->setPaymentTitle($paymentService->getTitle());
        return $this;
    }

    public function setItemInfo($order, $item)
    {
        $product = $item->getProduct();

        $infoBuyRequest = $item->getBuyRequest()->getData();
        $this->getSubscriptionModel()->setOrderItemInfo($infoBuyRequest);
        $infoRequestDate = $infoBuyRequest['subscription_start_date'];
        $subscriptionStartDate = date("Y-m-d", strtotime($infoRequestDate));
        $this->getSubscriptionModel()->setSubscriptionStartDate($subscriptionStartDate);

        $groupedParentId = $this->subscriptionService->getGroupProductIdFromRequest($infoBuyRequest);
        if ($groupedParentId) {
            $groupProduct = $this->subscriptionService->getProductModel($groupedParentId);
            $subscriptionService = $this->getSubscriptionService($groupProduct, $infoBuyRequest);
        } else {
            $subscriptionService = $this->getSubscriptionService($product, $infoBuyRequest);
        }

        $subscriptionData = $subscriptionService->getSubscriptionData();

        $this->getSubscriptionModel()->setBillingPeriodLabel($subscriptionData->getData('billing_frequency_label'));
        $this->getSubscriptionModel()->setBillingFrequency($subscriptionData->getData('billing_frequency'));
        $this->getSubscriptionModel()->setBillingPeriod($this->intervalType[$subscriptionData->getBillingPeriod()]);
        $this->getSubscriptionModel()->setPeriodMaxCycles($subscriptionData->getData('billing_max_cycles'));
        $billingPeriodCycle = $subscriptionData->getData('billing_period_interval');
        if (!$billingPeriodCycle) {
            $billingPeriodCycle = $item->getBuyRequest()->getData('billing_period');
        }
        $this->getSubscriptionModel()->setBillingFrequencyCycle($billingPeriodCycle);
        $isUpdateBillingFrequencey = $subscriptionData->getData('billing_period_type') == 'customer' ?? false;
        $this->getSubscriptionModel()->setIsUpdateBillingFrequency($isUpdateBillingFrequencey);

        $this->getSubscriptionModel()->setIsTrial($subscriptionData->getData('allow_trial'));
        if ($subscriptionData->getData('allow_trial')) {
            $order->setHasTrial(true);
            $this->getSubscriptionModel()->setIsTrial(true);
            $this->getSubscriptionModel()->setTrialPeriodLabel($subscriptionData->getData('trial_period_label'));
            $this->getSubscriptionModel()->setTrialPeriodUnit($this->intervalType[$subscriptionData->getTrialPeriod()]);
            $this->getSubscriptionModel()->setTrialPeriodFrequency($subscriptionData->getData('trial_frequency'));
            $this->getSubscriptionModel()->setTrialPeriodMaxCycle($subscriptionData->getData('trial_max_cycle'));
            $baseTrialAmount = $subscriptionData->getData('trial_amount');
            $this->getSubscriptionModel()->setTrialBillingAmount($subscriptionService->getConvertedPrice($baseTrialAmount));
            $this->getSubscriptionModel()->setBaseTrialBillingAmount($baseTrialAmount);
        }
        $baseInitialAmount = $subscriptionData->getData('initial_amount');
        $this->getSubscriptionModel()->setBaseInitialAmount($baseInitialAmount);
        $this->getSubscriptionModel()->setInitialAmount($subscriptionService->getConvertedPrice($baseInitialAmount));

        /* Set Billed Amount */
        $baseBillingAmount = $this->getBaseBillingAmount($product, $item, $subscriptionData);
        $billAmount = $subscriptionService->getConvertedPrice($baseBillingAmount);
        $this->getSubscriptionModel()->setBillingAmount($billAmount);
        $this->getSubscriptionModel()->setBaseBillingAmount($baseBillingAmount);

        $productFinalPrice = $product->setSkipDiscount(1)->getFinalPrice($item->getQty());
        $subscriptionService->getSubscriptionDiscountAmount($productFinalPrice);
        $this->getSubscriptionModel()->setDiscountAmount($subscriptionData->getDiscountAmount());
        $this->getSubscriptionModel()->setBaseDiscountAmount($subscriptionData->getBaseDiscountAmount());

        $this->getSubscriptionModel()->setTaxAmount($item->getTaxAmount());
        $this->getSubscriptionModel()->setBaseTaxAmount($item->getBaseTaxAmount());
        $this->getSubscriptionModel()->setProductId($item->getProduct()->getId());
        $this->getSubscriptionModel()->setProductName($item->getProduct()->getName());

        $this->getSubscriptionModel()->setQtySubscribed($item->getQty());
        $this->getSubscriptionModel()->setProductSku(rtrim($item->getProduct()->getSku(), '-'));

        if ($groupedParentId) {
            $this->getSubscriptionModel()->setParentProductId($groupedParentId);
        }

        $product->setSkipDiscount(0)->setSkipValidateTrial(0);
        return $this;
    }

    public function getBaseBillingAmount($product, $item, $subscriptionData)
    {
        if ($product->getTypeId() == 'bundle') {
            if ($subscriptionData->getData('subscription_type') == 'either') {
                return $product->setSkipValidateTrial(1)->getFinalPrice($item->getQty());
            } else {
                $product->setSkipValidateTrial(1)->setSkipDiscount(1)->getFinalPrice($item->getQty());
                return $product->getFinalPrice($item->getQty());
            }
        }
        return $product->setSkipValidateTrial(1)->getFinalPrice($item->getQty());
    }

    public function setOrderInfo($order, $item)
    {
        $this->getSubscriptionModel()->setSubscriptionStatus(1);
        $this->getSubscriptionModel()->setInitialOrderId($order->getIncrementId());

        $customerBillingAddressId = $order->getBillingAddress()->getCustomerAddressId();
        $customerShippingAddressId = 0;
        if (!$item->getIsVirtual()) {
            $customerShippingAddressId = $order->getShippingAddress()->getCustomerAddressId();
        }

        $billingAddressId = $customerBillingAddressId ?: $customerShippingAddressId;
        if (!$billingAddressId) {
            $addresses = $order->getAddresses();
            if ($addresses && !empty($addresses[0])) {
                $billingAddressId = $addresses[0]['customer_address_id'];
            }
        }

        $this->getSubscriptionModel()->setBillingAddressId($billingAddressId);
        if (!$item->getIsVirtual()) {
            $shippingAddressId = $customerShippingAddressId ?: $billingAddressId;
            $this->getSubscriptionModel()->setShippingAddressId($shippingAddressId);
        }

        $this->getSubscriptionModel()->setBaseCurrencyCode($order->getBaseCurrencyCode());
        $this->getSubscriptionModel()->setCurrencyCode($order->getOrderCurrencyCode());

        $this->getSubscriptionModel()->setCustomerId($order->getCustomerId());
        $this->getSubscriptionModel()->setSubscriberName($order->getCustomerName());
        $this->getSubscriptionModel()->setSubscriberEmail($order->getCustomerEmail());

        $this->getSubscriptionModel()->setStoreId($order->getStoreId());

        $this->getSubscriptionModel()->setBaseShippingAmount($order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount());
        $this->getSubscriptionModel()->setShippingAmount($order->getShippingAmount() + $order->getShippingTaxAmount());
        $this->getSubscriptionModel()->setOrderIncrementId($order->getIncrementId());
        return $this;
    }

    public function getSubscriptionIncrementId()
    {
        $incrementInstance = $this->numericValue->setPrefix($this->getSubscriptionModel()->getStoreId())
            ->setPadLength(8)->setPadChar('0');
        return $incrementInstance->format($this->getSubscriptionModel()->getId());
    }

    public function sendEmail($order, $item)
    {
        if (!$this->subscriptionHelper->isNewSubscriptionEmailSend($order->getStoreId())) {
            return false;
        }

        $subscription = $this->getSubscriptionModel();

        $nextDate = $this->getDateFormatted($subscription->getSubscriptionStartDate(), 2, $order);
        $billDate = $this->getDateFormatted($subscription->getNextOccurrenceDate(), 2, $order);
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

        $this->subscriptionModel->sendEmail($emailVars, EmailService::EMAIL_NEW_SUBSCRIPTION);
    }

    /**
     * @param $date
     * @param boolean $appendTime
     * @return string
     * @throws LocalizedException
     */
    private function getUtcDateTime($date, $appendTime = true)
    {
        if ($appendTime) {
            $currentStoreTime = $this->timezone->date()->format(' H:i:s');
            $date .= $currentStoreTime;
        }

        return $this->timezone->convertConfigTimeToUtc($date);
    }

    private function calculateDiscount($product, $selection)
    {
        if ($product && $selection) {
            $selectionPrice = $selection->getFinalPrice();
            $price = $selection->getPrice();

            if (!$selectionPrice || ($selectionPrice == $price)) {
                $type = $product->getDiscountType();
                $discount = $product->getDiscountAmount();
                if ($type == 'percentage') {
                    $percentageAmount = $price * ($discount / 100);
                    $selectionPrice = $price - $percentageAmount;
                } else {
                    $selectionPrice = $price - $discount;
                }
            }
            return $this->subscriptionService->getConvertedPrice($selectionPrice);
        }
        return 0;
    }

    public function getDateFormatted($date, $format, $order)
    {
        return $this->timezone->formatDateTime(
            new \DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $order->getStore())
        );
    }
}
