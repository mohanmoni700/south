<?php

declare(strict_types=1);

namespace Alfakher\PaymentEdit\Helper;

use ParadoxLabs\TokenBase\Helper\Data as BaseData;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Model\Method\Factory;
use Magento\Store\Model\App\Emulation;
use Magento\Payment\Model\Config;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\WebsiteFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Helper\Session\CurrentCustomer;
use ParadoxLabs\TokenBase\Model\CardFactory;
use ParadoxLabs\TokenBase\Model\ResourceModel\Card\CollectionFactory;
use ParadoxLabs\TokenBase\Helper\Address;
use ParadoxLabs\TokenBase\Helper\Operation;

/**
 * Payment module helper
 * Class Data
 */
class Data extends BaseData
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param Factory $paymentMethodFactory
     * @param Emulation $appEmulation
     * @param Config $paymentConfig
     * @param Initial $initialConfig
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param WebsiteFactory $websiteFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param PaymentFactory $paymentFactory
     * @param Quote $backendSession *Proxy
     * @param CheckoutSession $checkoutSession *Proxy
     * @param CustomerSession $customerSession *Proxy
     * @param CurrentCustomer $currentCustomerSession *Proxy
     * @param CardFactory $cardFactory
     * @param CollectionFactory $cardCollectionFactory
     * @param Address $addressHelper *Proxy
     * @param Operation $operationHelper
     * @param Http $request
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        LayoutFactory $layoutFactory,
        Factory $paymentMethodFactory,
        Emulation $appEmulation,
        Config $paymentConfig,
        Initial $initialConfig,
        State $appState,
        StoreManagerInterface $storeManager,
        Registry $registry,
        WebsiteFactory $websiteFactory,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        PaymentFactory $paymentFactory,
        Quote $backendSession,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        CurrentCustomer $currentCustomerSession,
        CardFactory $cardFactory,
        CollectionFactory $cardCollectionFactory,
        Address $addressHelper,
        Operation $operationHelper,
        Http $request,
        OrderFactory $orderFactory
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
