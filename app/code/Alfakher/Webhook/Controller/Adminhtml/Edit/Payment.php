<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartManagementInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class Payment extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Payment
{
    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $params;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

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
     * @param CartManagementInterface $cartManagement
     * @param DataObjectFactory $dataObjectFactory
     * @param Onepage $onepageCheckout
     * @param JsonHelper $jsonHelper
     * @param LoggerInterface $logger
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
        CartManagementInterface $cartManagement,
        DataObjectFactory $dataObjectFactory,
        Onepage $onepageCheckout,
        JsonHelper $jsonHelper,
        LoggerInterface $logger
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
            $cartManagement,
            $dataObjectFactory,
            $onepageCheckout,
            $jsonHelper,
            $logger
        );
        $this->cartManagement    = $cartManagement;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->logger            = $logger;
        $this->params            = $this->getRequest()->getParams();
    }

    /**
     * @inheritDoc
     */
    protected function update()
    {
        $this->updatePaymentMethod();
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
