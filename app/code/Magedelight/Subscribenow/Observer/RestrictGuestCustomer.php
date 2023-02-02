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
namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\SessionFactory;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;

/**
 * RestrictGuestCustomer
 *
 * Check if Guest user is checkout with subscription
 * product then it should be redirect to login page
 */
class RestrictGuestCustomer implements ObserverInterface
{
    
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    
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
    
    /**
     * @var SessionFactory
     */
    private $customerSession;
    
    /**
     * * Constructor
     *
     * @param Data $helper
     * @param RequestInterface $request
     * @param Cart $cart
     * @param SubscriptionService $subscriptionService
     * @param SessionFactory $customerSession
     * @param Context $context
     */
    public function __construct(
        Data $helper,
        RequestInterface $request,
        ManagerInterface $messageManager,
        ResponseInterface $responseFactory,
        UrlInterface $url,
        Cart $cart,
        SubscriptionService $subscriptionService,
        SessionFactory $customerSession
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
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
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $isModuleEnabled = $this->helper->isModuleEnable();
        $isSubscriptionItem = $this->hasCartSubscriptionItem();
        $customerSession = $this->customerSession->create();
        
        if (!$isModuleEnabled || !$isSubscriptionItem) {
            return $this;
        }
        
        if ($isSubscriptionItem && !$customerSession->isLoggedIn()) {
            $loginURL = $this->url->getUrl('customer/account/login/');
            $chcekoutURL = $this->url->getUrl('checkout');
            
            $this->messageManager->addNoticeMessage(__('You must be registered in order to purchase subscription Item'));
            $customerSession->setUseNotice(true);
            $customerSession->setBeforeAuthUrl($chcekoutURL);
            $customerSession->setAfterAuthUrl($chcekoutURL);

            return $this->responseFactory->setRedirect($loginURL);
        }
    }
}
