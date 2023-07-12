<?php

namespace Magedelight\Subscribenow\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\UrlInterface;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;

class AccountManagement
{
    const BLACK_LIST_CUSTOMER_GROUP = 4;

     /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Data
     */
    private $helper;
    
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var SubscriptionService
     */
    private $subscriptionService;

    public function __construct(
        ManagerInterface $messageManager,
        ResponseInterface $responseFactory,
        SessionFactory $customerSession,
        UrlInterface $url,
        Data $helper,
        Cart $cart,
        SubscriptionService $subscriptionService
    ) {
        $this->messageManager = $messageManager;
        $this->responseFactory = $responseFactory;
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @return bool
     */
    private function hasCartSubscriptionItem()
    {
        $isSubscriptionItem = false;
        $items = $this->cart->getQuote()->getAllVisibleItems();
        
        foreach ($items as $item) {
            if ($this->subscriptionService->isSubscribed($item)) {
                $isSubscriptionItem = true;
                break;
            }
        }
        
        return $isSubscriptionItem;
    }

    /**
     * Authenticate a customer
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterface $result
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterAuthenticate(
        \Magento\Customer\Api\AccountManagementInterface $subject,
        $result
    )
    {
        $isModuleEnabled = $this->helper->isModuleEnable();
        $isSubscriptionItem = $this->hasCartSubscriptionItem();
        $allowedCustomerGroup = $this->helper->getAllowedCustomerGroups();
        $noSubscription = $this->helper->firstSubscriptionLabel();
        $cartURL = $this->url->getUrl('checkout/cart');
        $cartURL = "<a href='".$this->url->getUrl('checkout/cart')."'>cart page</a>";

        if (!$isModuleEnabled || !$isSubscriptionItem) {
            return $result;
        }

        if($isSubscriptionItem && !in_array($result->getGroupId(), $allowedCustomerGroup)) {
            throw new LocalizedException(__("You are restricted to purchase products with subscriptions. Kindly contact merchant for further help. Kindly visit cart page to change it to $noSubscription or remove subscription product from the cart."));
          //  return $this->responseFactory->setRedirect($cartURL);
        }
        return $result;
    }
}