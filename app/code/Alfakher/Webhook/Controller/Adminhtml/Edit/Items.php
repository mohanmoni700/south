<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteDataBackupRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Magento\Quote\Api\CartRepositoryInterface;

class Items extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Items
{
    /**
     * @var QuoteDataBackupRepositoryInterface
     */
    private $backupRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    
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
     * @param QuoteDataBackupRepositoryInterface $backupRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct( //NOSONAR
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
        QuoteDataBackupRepositoryInterface $backupRepository,
        CartRepositoryInterface $cartRepository
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
            $backupRepository
        );
        $this->backupRepository = $backupRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritDoc
     */
    protected function update()
    {
        $this->updateOrderItems();
        try {
            $order = $this->getOrder();
            $quote = $this->cartRepository->get($order->getQuoteId());
            $quote->collectTotals()->save();
            $quoteBackup = $this->backupRepository->getByQuoteId($order->getQuoteId());
            $this->backupRepository->delete($quoteBackup);
            
            /* Start - New event added*/
            $this->_eventManager->dispatch(
                'blueedit_save_after',
                [
                    'item' => $order
                ]
            );
            /* end - New event added*/
            /* Start - New event added for tax calculation*/
            $this->_eventManager->dispatch(
                'mageworx_order_edit_after',
                [
                    'order' => $order,
                    'quote' => $quote,
                ]
            );
            /* end - New event added for tax calculation*/
        } catch (NoSuchEntityException $e) {
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
