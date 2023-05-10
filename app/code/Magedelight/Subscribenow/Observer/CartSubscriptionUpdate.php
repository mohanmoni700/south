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

namespace Magedelight\Subscribenow\Observer;

use Magedelight\Subscribenow\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class CartSubscriptionUpdate implements ObserverInterface
{

    /**
     * Customer group cache context
     */
    const CONTEXT_GROUP = 'customer_group';

    /**
     * @var Data
     */
    protected $subscriberHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @param Data $subscriberHelper
     * @param ManagerInterface $messageManager
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Data $subscriberHelper,
        ManagerInterface $messageManager,
        ProductRepository $productRepository,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->subscriberHelper = $subscriberHelper;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->httpContext = $httpContext;
    }

    /**
     * This will remove additional_options to cart item when ordering
     * from admin panel which is default magento adding
     *
     * @param Observer $observer
     * @return CartSubscriptionUpdate
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->subscriberHelper->isModuleEnable()) {

            $exist = true;
            
            $customerLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
            $customerGroupId = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP);

            /** For Not Allowed Customer Group For Subscription **/
            if ($this->subscriberHelper->isAllowToAddtoCart() && $customerLoggedIn && !in_array($customerGroupId, $this->subscriberHelper->getAllowedCustomerGroups())) :
                $this->messageManager->addErrorMessage(__('You are restricted to purchase products with subscriptions. Kindly contact merchant for further help.'));
                return;
            endif;
            /** For Not Allowed Customer Group For Subscription **/

            /** For Guest Users and Not allowed Add to cart for subscription product **/
            if (!$this->subscriberHelper->isAllowToAddtoCart() && !in_array($customerGroupId, $this->subscriberHelper->getAllowedCustomerGroups())) :
                $exist = false;
            endif;
            /** For Guest Users and Not allowed Add to cart for subscription product **/

            if ($exist) {
                try {
                        $cart = $observer->getCart();
                        $infoDataObject = $observer->getInfo()->getData();

                        foreach ($infoDataObject as $itemId => $itemInfo) {
                            $item = $cart->getQuote()->getItemById($itemId);
                            if (!$item) {
                                continue;
                            }

                            if ($this->isUpdateNeeded($item, $itemInfo)) {
                                $cart->removeItem($itemId);

                                $productId = $item->getProduct()->getId();
                                $product = $this->productRepository->getById($productId, false, null, true);
                                $params = $this->prepareParams($item, $itemInfo);
                                $cart->addProduct($product, $params);

                                $this->messageManager->addSuccessMessage(__('Shopping Cart Updated'));
                            }
                        }
                } catch (LocalizedException $e) {
                    throw new LocalizedException(__($e->getMessage()));
                } catch (\Exception $e) {
                    throw new LocalizedException(__($e->getMessage()));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Products with subscriptions cannot be purchased without a login. Kindly login to purchase subscription products.'));
                $this->messageManager->addSuccessMessage(__('We have updated your cart with one time purchase/No subscription options instead of subscription.'));
            }
        }

        return $this;
    }

    /**
     * @param $item
     * @param $data
     * @return bool
     */
    protected function isUpdateNeeded($item, $data) : bool
    {
        $buyRequest = $item->getBuyRequest()->getData();

        $itemSubscribed = $buyRequest['options']['_1'] ?? false;
        $subscriptionPostData = $data['subscription'] ?? [];

        $postDataSubscriptionOption = $subscriptionPostData['_1'] ?? false;
        $postDataBillingPeriod = $subscriptionPostData['billing_period'] ?? false;
        $postDataStartDate = $subscriptionPostData['subscription_start_date'] ?? false;
        $postDataEndType = $subscriptionPostData['end_type'] ?? false;
        $postDataEndDate = $subscriptionPostData['subscription_end_date'] ?? false;
        $postDataEndCycle = $subscriptionPostData['subscription_end_cycle'] ?? false;

        if ($subscriptionPostData) {
            if (!$itemSubscribed) {
                return true;
            }

            if ($postDataSubscriptionOption
                && $buyRequest['options']['_1'] != $postDataSubscriptionOption) {
                return true;
            }

            if ($postDataBillingPeriod
                && $buyRequest['billing_period'] != $postDataBillingPeriod) {
                return true;
            }

            if ($postDataStartDate
                && $buyRequest['subscription_start_date'] != $postDataStartDate) {
                return true;
            }

            if ($postDataEndType
                && $buyRequest['end_type'] != $postDataEndType) {
                return true;
            }

            if ($postDataEndDate
                && $buyRequest['subscription_end_date'] != $postDataEndDate) {
                return true;
            }

            if ($postDataEndCycle
                && $buyRequest['subscription_end_cycle'] != $postDataEndCycle) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $item
     * @param $data
     * @return array
     */
    protected function prepareParams($item, $data) : array
    {
        $params = $item->getBuyRequest()->getData();

        if ($data['subscription']['_1'] == 'subscription') {
            $params['options']['_1'] = $data['subscription']['_1'];

            if (isset($data['subscription']['billing_period'])) {
                $params['billing_period'] = $data['subscription']['billing_period'];
            }

            if (isset($data['subscription']['subscription_start_date'])) {
                $params['subscription_start_date'] = $data['subscription']['subscription_start_date'];
            }

            if (isset($data['subscription']['end_type'])) {
                $params['end_type'] = $data['subscription']['end_type'];
            }

            if (isset($data['subscription']['subscription_end_date'])) {
                $params['subscription_end_date'] = $data['subscription']['subscription_end_date'];
            }

            if (isset($data['subscription']['subscription_end_cycle'])) {
                $params['subscription_end_cycle'] = $data['subscription']['subscription_end_cycle'];
            }
        } else {
            if (isset($params['options']['_1'])) {
                unset($params['options']['_1']);
            }

            if (isset($params['billing_period'])) {
                unset($params['billing_period']);
            }

            if (isset($params['subscription_start_date'])) {
                unset($params['subscription_start_date']);
            }

            if (isset($params['end_type'])) {
                unset($params['end_type']);
            }

            if (isset($params['subscription_end_date'])) {
                unset($params['subscription_end_date']);
            }

            if (isset($params['subscription_end_cycle'])) {
                unset($params['subscription_end_cycle']);
            }
        }

        return $params;
    }
}
