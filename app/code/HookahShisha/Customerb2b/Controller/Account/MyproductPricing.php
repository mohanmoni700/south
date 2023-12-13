<?php

declare(strict_types=1);

namespace HookahShisha\Customerb2b\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class adding the product pricing tab on customer account
 */
class MyproductPricing extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $session
     * @param ScopeConfigInterface $scopeConfig
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context              $context,
        PageFactory          $resultPageFactory,
        Session              $session,
        ScopeConfigInterface $scopeConfig,
        ForwardFactory       $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $session;
        $this->scopeConfig = $scopeConfig;
        $this->resultForwardFactory = $resultForwardFactory;
        return parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // Check if the configuration is set and has a specific value
        $isProductPricingEnabled = $this->scopeConfig->getValue(
            'hookahshisha/my_product_pricing_configuration/is_disable',
            ScopeInterface::SCOPE_STORE
        );

        if (!$isProductPricingEnabled) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        if ($this->_customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set('My Product Pricing');
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login/');
            return $resultRedirect;
        }
    }
}
