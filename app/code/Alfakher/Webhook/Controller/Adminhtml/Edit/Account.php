<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\OrderEditor\Api\Data\OrderManager\CustomerInfoInterfaceFactory;
use MageWorx\OrderEditor\Api\OrderManager\CustomerInfoManagerInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\Customer as OrderEditorCustomerModel;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class Account extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Account
{
    /**
     * @var customerInfoManager
     */
    private $customerInfoManager;

    /**
     * @var customerInfoFactory
     */
    private $customerInfoFactory;

    /**
     * Body constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     * @param OrderRepositoryInterface $orderRepository
     * @param MsiStatusManager $msiStatusManager
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param SerializerJson $serializer
     * @param OrderEditorCustomerModel $customer
     * @param CustomerInfoManagerInterface $customerInfoManager
     * @param CustomerInfoInterfaceFactory $customerInfoFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        QuoteRepositoryInterface $quoteRepository,
        ShippingModel $shipping,
        PaymentModel $payment,
        OrderRepositoryInterface $orderRepository,
        MsiStatusManager $msiStatusManager,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        SerializerJson $serializer,
        OrderEditorCustomerModel $customer,
        CustomerInfoManagerInterface $customerInfoManager,
        CustomerInfoInterfaceFactory $customerInfoFactory
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $helper,
            $scopeConfig,
            $quoteRepository,
            $shipping,
            $payment,
            $orderRepository,
            $msiStatusManager,
            $inventoryDetectionStatusManager,
            $serializer,
            $customer,
            $customerInfoManager,
            $customerInfoFactory
        );
        $this->customerInfoManager = $customerInfoManager;
        $this->customerInfoFactory = $customerInfoFactory;
    }

    /**
     * @inheritDoc
     */
    protected function update()
    {
        $order        = $this->loadOrder();
        $customerId   = $this->getCustomerId();
        $customerData = $this->getCustomerData();

        $customerInfo = $this->customerInfoFactory->create();
        if (!empty($customerData['email'])) {
            $customerInfo->setCustomerEmail($customerData['email']);
        }
        if (!empty($customerData['customer_firstname'])) {
            $customerInfo->setCustomerFirstname($customerData['customer_firstname']);
        }
        if (!empty($customerData['customer_lastname'])) {
            $customerInfo->setCustomerLastname($customerData['customer_lastname']);
        }
        if (!empty($customerId)) {
            $customerInfo->setCustomerId($customerId);
        }
        if (isset($customerData['group_id'])) {
            $customerInfo->setCustomerGroup((int)$customerData['group_id']);
        }

        $this->customerInfoManager->updateCustomerInfoByOrderId(
            $order->getId(),
            $customerInfo
        );

        // Drop existing order because it recently updated but does not store new info
        $this->clearOrder();

        /* Start - New event added*/
        $this->_eventManager->dispatch(
            'blueedit_save_after',
            [
                'item' => $this->getOrder()
            ]
        );
        /* end - New event added*/
    }
}
