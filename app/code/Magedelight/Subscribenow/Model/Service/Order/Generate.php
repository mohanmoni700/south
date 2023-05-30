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

namespace Magedelight\Subscribenow\Model\Service\Order;

use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magedelight\Subscribenow\Model\ProductSubscribers;
use Magedelight\Subscribenow\Model\Service\PaymentService;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSenderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Generate
{
    /**
     * @var ProductSubscribers
     */
    private $subscriptionModel;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var PaymentService
     */
    private $paymentService;
    /**
     * @var SubscribeHelper
     */
    protected $subscribeHelper;

    private $orderSenderFactory;

    public $currentQuote = null;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;
    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $historyFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerFactory $customer,
        CartManagementInterface $cartManagement,
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        PaymentService $paymentService,
        SubscribeHelper $subscribeHelper,
        OrderSenderFactory $orderSenderFactory,
        Registry $registry,
        EventManager $eventManager,
        CurrencyFactory $currencyFactory,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->paymentService = $paymentService;
        $this->subscribeHelper = $subscribeHelper;
        $this->orderSenderFactory = $orderSenderFactory;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->currencyFactory = $currencyFactory;
        $this->historyFactory = $historyFactory;
    }

    public function getCurrentQuote()
    {
        return $this->currentQuote;
    }

    public function setCurrentQuoteNull()
    {
        $this->currentQuote = null;
    }

    /**
     * Validate order for Group Product before generate
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateOrder()
    {
        // for grouped parent product validation
        if ($this->getProfile()->getParentProductId()) {
            $product = $this->productRepository->getById(
                $this->getProfile()->getParentProductId(),
                false,
                $this->getProfile()->getStoreId(),
                true
            );

            if (!$product) {
                throw new LocalizedException(
                    __('Grouped Product that you are trying to add is not available.')
                );
            }

            if ($product && $product->getId() && $product->getStatus() != 1) {
                throw new LocalizedException(
                    __('Grouped Product that you are trying to add is not available.')
                );
            }
        }
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function generateOrder()
    {
        $this->validateOrder();

        if ($this->getProfile()->getSubscriptionStatus() == ProfileStatus::ACTIVE_STATUS) {
            $this->storeManager->setCurrentStore($this->getStore());

            $cart = $this->createEmptyCart();
            $this->currentQuote = $cart;

            $this->addProductToCart($cart);

            $cart->setCustomer($this->getCustomer()->getDataModel())
                ->setCustomerEmail($this->getCustomer()->getEmail());

            $cart->getBillingAddress()->addData($this->getProfileBillingAddress());
            $cart->getShippingAddress()->addData($this->getProfileShippingAddress());

            $this->setShippingMethod($cart);

            $cart->setPaymentMethod($this->getProfile()->getPaymentMethodCode());

            $cart->setSubscriptionParentId($this->getProfile()->getId());

            $payment = $this->paymentService->getBySubscription($this->getProfile());

            $this->eventManager->dispatch(
                'subscribenow_subscription_recurrence_before_submit',
                ['quote' => $cart, 'profile' => $this->getProfile(), 'product' => $this->getProduct()]
            );

            $cart->collectTotals()->save();

            if ($this->getProfile()->getPaymentMethodCode() == 'magedelight_ewallet') {
                if (!$payment->checkBalance($cart->getGrandTotal())) {
                    throw new LocalizedException(__('Insufficient funds in wallet'));
                }
                $this->deductAmountFromWallet($cart);
            }

            $cart->getPayment()->importData($payment->getImportData());

            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->cartManagement->submit($cart);
            $_order = $this->orderRepository->get($order->getId());
            $_order->setCustomerIsGuest(false);
            $this->orderRepository->save($_order);

            if (null == $order) {
                throw new LocalizedException(__('An error occurred on placing the order.'));
            }

            /** Add Order Comment With Profile Id */
            if ($order->getEntityId()) {
                $profile_id = '<a href="'.$this->storeManager->getStore()->getBaseUrl().'subscribenow/account/summary/id/'.$this->getProfile()->getSubscriptionId().'/">'.$this->getProfile()->getProfileId().'</a>';
                $comment = __("Order has been placed from Subscription profile ".$profile_id.".");
                $status = $order->getStatus();
                $history = $this->historyFactory->create();
                $history->setComment($comment);
                $history->setParentId($order->getEntityId());
                $history->setIsVisibleOnFront(1);
                $history->setIsCustomerNotified(0);
                $history->setEntityName('order');
                $history->setStatus($status);
                $history->save();
            }

            $this->sendOrderEmail($order);
            $this->getProfile()
                ->setOrderIncrementId($order->getIncrementId())
                ->afterSubscriptionCreate()
                ->save();

            $this->setCurrentQuoteNull();

            return $order;
        }
    }

    public function addProductToCart($cart)
    {
        $quoteItem = $cart->addProduct($this->getProduct(), $this->getBuyRequest());

        if (!is_object($quoteItem)) {
            throw new LocalizedException(__($quoteItem));
        }

        $quoteItem->setSubscriptionOrderGenerate(1);

        if (!$this->subscribeHelper->useDynamicPrice()) {
            $quoteItem->setCustomPrice($this->getProfile()->getBillingAmount()); // showing discounted price
            $quoteItem->setOriginalCustomPrice($this->getProfile()->getBillingAmount()); // setting product subtotal
        }

        if ($this->isProfileInTrialPeriod() && false) {
            $quoteItem->setName(__('Trial ') . $this->getProduct()->getName());
            $quoteItem->setCustomPrice(0);
            $quoteItem->setOriginalCustomPrice(0);
        }
    }

    public function deductAmountFromWallet($quote)
    {
        $quote->setData('used_checkout_wallet_amout', $quote->getGrandTotal());
        $quote->setData('base_used_checkout_wallet_amout', $quote->getBaseGrandTotal());
        $quote->setGrandTotal(0);
        $quote->setBaseGrandTotal(0);
    }

    /**
     * @return mixed
     */
    public function getProfileBillingAddress()
    {
        $address = $this->getCustomer()->getAddressById($this->getProfile()->getBillingAddressId());
        $address->setCustomer($this->getCustomer())
            ->setSaveInAddressBook(0);
        return $address->getData();
    }

    /**
     * @return mixed
     */
    public function getProfileShippingAddress()
    {
        $address = $this->getCustomer()->getAddressById($this->getProfile()->getShippingAddressId());
        $address->setCustomer($this->getCustomer())
            ->setSaveInAddressBook(0);
        return $address->getData();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        $product = $this->productRepository->getById(
            $this->getProfile()->getProductId(),
            false,
            $this->getProfile()->getStoreId(),
            true
        );

        $product->setInitialAmount(0);
        $product->setSkipFutureSubscriptionValidation(true);
        if ($this->isProfileInTrialPeriod()) {
            $product->setAllowTrial(1);
        } else {
            $product->setAllowTrial(0);
        }

        if (!$this->subscribeHelper->useDynamicPrice()) {
            $prices = $this->getProfilePrice();
            foreach ($prices as $key => $price) {
                $product->setData($key, $price);
            }

            if ($product->getTypeId() == 'bundle' && !$product->getAllowTrial()) {
                $bundleOptions = $this->getProfile()->getAdditionalInfo('product_options');

                if ($bundleOptions && !empty($bundleOptions['bundle_options'])) {
                    $product->setData('is_subscription_recurring_order', true);
                    $product->setData(
                        'subscription_bundle_option',
                        $bundleOptions['bundle_options']
                    );
                }
            }
        }

        return $product;
    }

    public function getProfilePrice()
    {
        return [
            'trial_amount' => $this->getProfile()->getBaseTrialBillingAmount()
        ];
    }

    /**
     * @return \Magento\Framework\DataObject
     * @throws NoSuchEntityException
     */
    public function getBuyRequest()
    {
        $profileItemInfo = $this->getProfile()->getOrderItemInfo();

        $buyRequest = new \Magento\Framework\DataObject($profileItemInfo);
        $buyRequest->unsetData(['form_key', 'related_product', 'subscription_start_date']);
        $buyRequest->setQty((float) $this->getProfile()->getQtySubscribed());

        if ($this->isProfileInTrialPeriod()) {
            $options = $buyRequest->getData('options');
            $options['label'] = __('Payment Type');
            $options['value'] = __('Trial period payment');
            $buyRequest->setData('options', $options);
        }
        return $buyRequest;
    }

    public function setPassedOccurrence()
    {
        if ($this->isProfileInTrialPeriod()) {
            $this->getProfile()->setTrialCount($this->getProfile()->getTrialCount() + 1);
        } else {
            $this->getProfile()->setTotalBillCount($this->getProfile()->getTotalBillCount() + 1);
        }
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isProfileInTrialPeriod()
    {
        return $this->getProfile()->getIsTrial() && $this->getProfile()->isTrialPeriod();
    }

    private function setProfileRegistry($subscription)
    {
        if ($this->registry->registry('postdata')) {
            $this->registry->unregister('postdata');
        }
        if ($this->registry->registry('current_profile')) {
            $this->registry->unregister('current_profile');
        }
        $this->registry->register('current_profile', $subscription);
    }

    /**
     * @param ProductSubscribers $subscription
     * @return $this
     */
    public function setProfile($subscription)
    {
        $this->subscriptionModel = $subscription;
        $this->setProfileRegistry($subscription);
        return $this;
    }

    /**
     * @return ProductSubscribers
     */
    public function getProfile()
    {
        return $this->subscriptionModel;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $currency = $this->currencyFactory->create()->load($this->getProfile()->getCurrencyCode());

        return $this->storeManager
            ->getStore($this->getProfile()->getStoreId())
            ->setCurrentCurrency($currency)
            ->setCurrentCurrencyCode($this->getProfile()->getCurrencyCode());
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        $customerId = $this->getProfile()->getCustomerId();
        return $this->customer->create()->load($customerId);
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     */
    protected function createEmptyCart()
    {
        $cartId = $this->cartManagement->createEmptyCart($this->getCustomer()->getId());
        return $this->cartRepository->get($cartId);
    }

    /**
     * Send Order Email
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function sendOrderEmail($order)
    {
        if (!$this->subscribeHelper->isSubscriptionOrderEmailSend($order->getStoreId())) {
            return false;
        }

        $emailSender = $this->orderSenderFactory->create();
        try {
            $emailSender->send($order, true);
        } catch (\Exception $ex) {
        }
    }

    /**
     * Set Shipping Method
     */
    public function setShippingMethod($cart)
    {
        if ($cart->isVirtual()) {
            return $this;
        }

        $autoSelect = $this->subscribeHelper->isAutoSelectShippingMethod();
        if ($autoSelect) {
            $cart->collectTotals();
            $shippingAddress = $cart->getShippingAddress();
            $shippingAddress->collectShippingRates();

            $methods = [];
            $getAvailableMethods = $shippingAddress->getAllShippingRates();
            foreach ($getAvailableMethods as $method) {
                $methods[$method->getPrice()] = $method->getCode();
            }

            $shippingMethod = $this->getProfile()->getShippingMethodCode();
            if ($methods) {
                $key = min(array_keys($methods));
                $shippingMethod = $methods[$key];
            }

            $cart->getShippingAddress()
                ->setShippingMethod($shippingMethod);

            $cart->setTotalsCollectedFlag(false)->collectTotals()->save();
        } else {
            $cart->getShippingAddress()
                ->setShippingMethod($this->getProfile()->getShippingMethodCode())
                ->setCollectShippingRates(true);
        }
        return $this;
    }
}
