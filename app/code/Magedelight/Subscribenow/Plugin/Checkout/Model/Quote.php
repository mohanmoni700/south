<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin\Checkout\Model;

use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magedelight\Subscribenow\Model\Subscription;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\OrderRepository;

class Quote
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var State
     */
    private $state;

    /**
     * Quote Plugin Constructor
     *
     * @param Http $request
     * @param SubscriptionHelper $subscriptionHelper
     * @param Subscription $subscription
     * @param DataObjectFactory $objectFactory
     * @param OrderRepository $orderRepository
     * @param Session $customerSession
     * @param State $state
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Http $request,
        SubscriptionHelper $subscriptionHelper,
        Subscription $subscription,
        DataObjectFactory $objectFactory,
        OrderRepository $orderRepository,
        Session $customerSession,
        State $state,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscription = $subscription;
        $this->objectFactory = $objectFactory;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->state = $state;
    }

    /**
     * Add Subscription Info Summary
     *
     * @param object $subject
     * @param object $product
     *
     * @param null $request
     * @return array|Quote $product
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAddProduct($subject, $product, $request = null)
    {
        if (!$this->subscriptionHelper->isModuleEnable()) {
            return [$product, $request];
        }
        if ($this->isSubscriptionItem($product)) {
            throw new LocalizedException(
                __('You need to choose subscription options for this item.')
            );
        }
        if ($product->getIsSubscription()) {
            if ($product->getSubscriptionType() == PurchaseOption::SUBSCRIPTION) {
                $areaCode = $this->state->getAreaCode();
                /*restricting subscription as per customer group (Not For Admin)*/
                if ($areaCode != 'adminhtml') {
                    $currentCustomerGroup = $this->customerSession->getCustomerGroupId();
                    $allowedCustomerGroup = $this->subscriptionHelper->getAllowedCustomerGroups();
                    if($this->subscriptionHelper->isAllowToAddtoCart()){
                        $guestid = 0;
                        array_push($allowedCustomerGroup,$guestid);
                    }
                    if (!in_array($currentCustomerGroup, $allowedCustomerGroup)) {
                        $message = $this->subscriptionHelper->getNotAllowedCustomerMessage();
                        throw new LocalizedException(__($message));
                    }
                }
            }
        }

        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }

        if (!$request instanceof DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if ($this->getSubscription()->isAdd($product, $request)) {
            $subscription = $this->getSubscription()->getData($product, $request)->getSubscriptionData();

            if ($request) {
                $options = $request->getData('options');
                $options['_1'] = 'subscription';
                $request->setData('options', $options);
            }

            if ($request && !$request->getSubscriptionStartDate()) {
                $request->setData('subscription_start_date', $subscription->getSubscriptionStartDate());
            }

            $additionalInfo = $this->getSubscription()->getBuildInfo($subscription, $request);
            $product->addCustomOption('additional_options', $additionalInfo);
        }
    }

    /**
     * Get Subscription Model
     *
     * @return \Magedelight\Subscribenow\Model\Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param null $product
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isSubscriptionItem($product = null)
    {
        $orderId = $this->request->getParam('order_id');
        $isSubscribeItem = false;

        if ($orderId && $product && $product->getIsSubscription()
            && $this->request->getFullActionName() == 'sales_order_reorder'
        ) {
            $order = $this->orderRepository->get($orderId);
            $items = $order->getItems();

            foreach ($items as $item) {
                if ($this->getSubscription()->getService()->isSubscribed($item)) {
                    $isSubscribeItem = true;
                    break;
                }
            }
        }
        return $isSubscribeItem;
    }
}
