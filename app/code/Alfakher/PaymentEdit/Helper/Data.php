<?php

namespace Alfakher\PaymentEdit\Helper;

/**
 * Payment module helper
 * Class Data
 */
class Data extends \ParadoxLabs\TokenBase\Helper\Data
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Model\Quote\PaymentFactory $paymentFactory
     * @param \Magento\Backend\Model\Session\Quote $backendSession *Proxy
     * @param \Magento\Checkout\Model\Session $checkoutSession *Proxy
     * @param \Magento\Customer\Model\Session $customerSession *Proxy
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomerSession *Proxy
     * @param \ParadoxLabs\TokenBase\Model\CardFactory $cardFactory
     * @param \ParadoxLabs\TokenBase\Model\ResourceModel\Card\CollectionFactory $cardCollectionFactory
     * @param \ParadoxLabs\TokenBase\Helper\Address $addressHelper *Proxy
     * @param \ParadoxLabs\TokenBase\Helper\Operation $operationHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $paymentFactory,
        \Magento\Backend\Model\Session\Quote $backendSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomerSession,
        \ParadoxLabs\TokenBase\Model\CardFactory $cardFactory,
        \ParadoxLabs\TokenBase\Model\ResourceModel\Card\CollectionFactory $cardCollectionFactory,
        \ParadoxLabs\TokenBase\Helper\Address $addressHelper,
        \ParadoxLabs\TokenBase\Helper\Operation $operationHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {

        $this->request = $request;
        $this->orderFactory = $orderFactory;
        parent::__construct(
            $context,
            $layoutFactory,
            $paymentMethodFactory,
            $appEmulation,
            $paymentConfig,
            $initialConfig,
            $appState,
            $storeManager,
            $registry,
            $websiteFactory,
            $customerFactory,
            $customerRepository,
            $paymentFactory,
            $backendSession,
            $checkoutSession,
            $customerSession,
            $currentCustomerSession,
            $cardFactory,
            $cardCollectionFactory,
            $addressHelper,
            $operationHelper
        );
    }

    /**
     * Get current customer in the adminhtml scope. Looks at order, quote, invoice, credit memo.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCurrentBackendCustomer()
    {
        $customer = $this->customerFactory->create();

        if ($this->registry->registry('current_order') != null
            && $this->registry->registry('current_order')->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById(
                $this->registry->registry('current_order')->getCustomerId()
            );
        } elseif ($this->registry->registry('current_invoice') != null
            && $this->registry->registry('current_invoice')->getOrder()->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById(
                $this->registry->registry('current_invoice')->getOrder()->getCustomerId()
            );
        } elseif ($this->registry->registry('current_creditmemo') != null
            && $this->registry->registry('current_creditmemo')->getOrder()->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById(
                $this->registry->registry('current_creditmemo')->getOrder()->getCustomerId()
            );
        } elseif ($this->backendSession->hasQuoteId()) {
            if ($this->backendSession->getQuote()->getCustomerId() > 0) {
                $customer = $this->customerRepository->getById(
                    $this->backendSession->getQuote()->getCustomerId()
                );
            } elseif ($this->backendSession->getQuote()->getCustomerEmail() != '') {
                $customer->setEmail($this->backendSession->getQuote()->getCustomerEmail());
            }
        }

        if (!$this->getIsFrontend()) {
            $orderId = $this->request->getParam('order_id');
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                $customer = $this->customerRepository->getById($order->getCustomerId());
            }
        }

        return $customer;
    }
}
