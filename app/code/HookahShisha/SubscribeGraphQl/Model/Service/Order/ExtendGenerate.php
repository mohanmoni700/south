<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Service\Order;

use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magedelight\Subscribenow\Model\Service\Order\Generate;
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
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSenderFactory;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;

class ExtendGenerate extends Generate
{
    private StoreManagerInterface $storeManager;

    private EventManager $eventManager;

    private CartManagementInterface $cartManagement;

    private OrderRepositoryInterface $orderRepository;

    private PaymentService $paymentService;

    private OrderFactory $orderFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customer
     * @param CartManagementInterface $cartManagement
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param PaymentService $paymentService
     * @param SubscribeHelper $subscribeHelper
     * @param OrderSenderFactory $orderSenderFactory
     * @param Registry $registry
     * @param EventManager $eventManager
     * @param CurrencyFactory $currencyFactory
     * @param HistoryFactory $historyFactory
     * @param OrderFactory $orderFactory
     */
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
        HistoryFactory $historyFactory,
        OrderFactory $orderFactory
    ) {
        parent::__construct(
            $storeManager,
            $customer,
            $cartManagement,
            $cartRepository,
            $orderRepository,
            $searchCriteriaBuilder,
            $productRepository,
            $paymentService,
            $subscribeHelper,
            $orderSenderFactory,
            $registry,
            $eventManager,
            $currencyFactory,
            $historyFactory
        );
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->cartManagement = $cartManagement;
        $this->orderRepository = $orderRepository;
        $this->paymentService = $paymentService;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return mixed
     */
    public function getProfileBillingAddress()
    {
        $address = $this->getCustomer()->getAddressById($this->getProfile()->getBillingAddressId());
        $addressId = $address->getId();

        //Whenever the billing addressId is null, order billing id is used
        if (!isset($addressId)) {
            $order = $this->getOrderById();
            return $order->getBillingAddress()->getData();
        }

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
        $addressId = $address->getId();

        //Whenever the shipping addressId is null, order shipping id is used
        if (!isset($addressId)) {
            $order = $this->getOrderById();
            return $order->getShippingAddress()->getData();
        }

        $address->setCustomer($this->getCustomer())
            ->setSaveInAddressBook(0);
        return $address->getData();
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

            //if shipping method is not same as the existing shipping method
            $shippingAddress = $cart->getShippingAddress()->getShippingMethod();
            if ($shippingAddress != $this->getProfile()->getShippingMethodCode()) {
                $cart->getShippingAddress()
                    ->setShippingMethod($this->getProfile()->getShippingMethodCode())
                    ->setCollectShippingRates(true);
            }

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
                $profile_id = '<a href="'.$this->storeManager->getStore()->getBaseUrl().
                    'subscribenow/account/summary/id/'.$this->getProfile()->getSubscriptionId().'/">' .
                    $this->getProfile()->getProfileId().'</a>';
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

    /**
     * @return Order
     */
    public function getOrderById()
    {
        $incrementId = $this->getProfile()->getInitialOrderId();
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }
}
