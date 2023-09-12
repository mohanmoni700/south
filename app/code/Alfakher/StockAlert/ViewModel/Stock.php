<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class Stock implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        Session               $customerSession,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Is customer logged in
     *
     * @return bool
     */
    public function canShowPopup(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get Store Id
     *
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return true if guest user alert is available
     *
     * @return mixed
     */
    public function isGuestAlertEnabled()
    {
        return $this->scopeConfig->getValue('catalog/productalert/enable_custom_alert_guest_user');
    }

    /**
     * Get Product ID
     *
     * @return mixed
     */

    public function getProductId()
    {
        return $this->request->getParam('id');
    }
}
