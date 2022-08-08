<?php
declare (strict_types = 1);

namespace Alfakher\ShippingEdit\Controller\Adminhtml\Form;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Store\Model\App\Emulation;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteDataBackupRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Api\RestoreQuoteInterface;
use MageWorx\OrderEditor\Model\Address;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Order;
use MageWorx\OrderEditor\Model\Quote;

class Load extends Action
{
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawFactory;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Order Editor helper
     *
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $blockId;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment\Method
     */
    protected $method;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var InventoryDetectionStatusManager
     */
    protected $inventoryDetectionStatusManager;

    /**
     * @var MsiStatusManager
     */
    private $msiStatusManager;

    /**
     * @var SerializerJson
     */
    protected $serializer;

    /**
     * @var RestoreQuoteInterface
     */
    protected $backupQuote;

    /**
     * @var QuoteDataBackupRepositoryInterface
     */
    protected $quoteBackupRepository;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * Load constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $rawFactory
     * @param \MageWorx\OrderEditor\Helper\Data $helperData
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment\Method $method
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteRepositoryInterface $quoteRepository
     * @param Address $address
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param MsiStatusManager $msiStatusManager
     * @param SerializerJson $serializer
     * @param RestoreQuoteInterface $backupQuote
     * @param QuoteDataBackupRepositoryInterface $quoteBackupRepository
     * @param Emulation $emulation
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\RawFactory $rawFactory,
        \MageWorx\OrderEditor\Helper\Data $helperData,
        \Magento\Framework\Registry $registry,
        \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment\Method $method,
        OrderRepositoryInterface $orderRepository,
        QuoteRepositoryInterface $quoteRepository,
        Address $address,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        MsiStatusManager $msiStatusManager,
        SerializerJson $serializer,
        RestoreQuoteInterface $backupQuote,
        QuoteDataBackupRepositoryInterface $quoteBackupRepository,
        Emulation $emulation
    ) {
        $this->rawFactory = $rawFactory;
        $this->pageFactory = $pageFactory;
        $this->helperData = $helperData;
        $this->coreRegistry = $registry;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->address = $address;
        $this->method = $method;

        $this->inventoryDetectionStatusManager = $inventoryDetectionStatusManager;
        $this->msiStatusManager = $msiStatusManager;

        $this->serializer = $serializer;
        $this->backupQuote = $backupQuote;
        $this->quoteBackupRepository = $quoteBackupRepository;
        $this->emulation = $emulation;
        return parent::__construct($context);
    }

    /**
     * Render block form
     *
     * @return ResultInterface
     * @throws \Exception
     */
    public function execute(): ResultInterface
    {
        /* start - changes for display shipping */
        $this->registerOrder();
        if ($this->getRequest()->getParam('block_id') === 'shipping_method') {
            $this->emulation->startEnvironmentEmulation($this->order->getStoreId(), 'adminhtml');
        }
        /* end - changes for display shipping */
        try {
            $this->msiStatusManager->disableMSI();
            $this->inventoryDetectionStatusManager->disableInventoryDetection();
            $response = [
                'result' => $this->getResultHtml(),
                'status' => true,
            ];
            $this->inventoryDetectionStatusManager->enableInventoryDetection();
        } catch (\Exception $e) {
            $response = [
                'result' => $e->getMessage() . ' ' . $e->getTraceAsString(),
                'error' => $e->getMessage(),
                'status' => false,
            ];
        } finally {
            $this->msiStatusManager->enableMSI();
        }

        if ($this->getRequest()->getParam('raw')) {
            $result = $this->rawFactory->create()->setContents($response['result']);
        } else {
            $result = $this->rawFactory->create()->setContents($this->serializer->serialize($response));
        }
        /* start - changes for display shipping */
        if ($this->getRequest()->getParam('block_id') === 'shipping_method') {
            $this->emulation->stopEnvironmentEmulation();
        }
        /* end - changes for display shipping */
        return $result;
    }

    /**
     * Html for edit block
     *
     * @return string
     */
    protected function getResultHtml(): string
    {
        $this->blockId = $this->getRequest()->getParam('block_id');

        $this->registerQuote();
        $this->registerAddress();

        if ($this->blockId === 'payment_method') {
            $this->method->setPaymentMethod();
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->addHandle('ordereditor_load_block_' . $this->blockId);

        return $resultPage->getLayout()->renderElement('content');
    }

    /**
     * Register order
     *
     * @return void
     * @throws LocalizedException
     */
    private function registerOrder(): void
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $this->order = $this->orderRepository->getById($orderId);
        $this->helperData->setOrder($this->order);
    }

    /**
     * Register quote
     *
     * @return void
     * @throws \Exception
     */
    private function registerQuote(): void
    {
        if ($this->blockId == 'shipping_method'
            || $this->blockId == 'payment_method'
            || $this->blockId == 'order_items'
        ) {
            $quoteId = (int) $this->order->getQuoteId();
            try {
                $this->quote = $this->quoteRepository->getById($quoteId);
                $this->quote->setOrigOrderId((int) $this->order->getId());
                $this->helperData->setQuote($this->quote);

                /* custom code */
                $this->_eventManager->dispatch('edit_shipperhq_before', [
                    'order' => $this->order,
                ]);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('Required quote with id %1 does not exist.', $quoteId));
                try {
                    $this->quote = $this->helperData->getQuote();
                    $this->helperData->setQuote($this->quote);
                } catch (LocalizedException $exception) {
                    $this->messageManager->addErrorMessage(__('Unable to recreate quote.', $quoteId));
                }
            }

            if ($this->blockId == 'order_items') {
                try {
                    $this->createQuoteBackup();
                } catch (LocalizedException $localizedException) {
                    $this->messageManager->addNoticeMessage(
                        __('Unable to backup quote: %1', $localizedException->getMessage())
                    );
                }
            }
        }
    }

    /**
     * Creating a quote backup
     *
     * @throws LocalizedException
     */
    private function createQuoteBackup(): void
    {
        try {
            $existingBackup = $this->quoteBackupRepository->getByQuoteId((int) $this->quote->getId());
            $backupExist = (bool) $existingBackup->getId();
        } catch (NoSuchEntityException $noSuchEntityException) {
            $backupExist = false;
        }

        if (!$backupExist) {
            $this->backupQuote->backupInitialQuoteState($this->quote);
        }
    }

    /**
     * Register order address
     *
     * @return void
     * @throws LocalizedException
     */
    private function registerAddress(): void
    {
        $addressId = 0;

        if ($this->blockId == 'billing_address') {
            $addressId = (int) $this->order->getBillingAddressId();
        } elseif ($this->blockId == 'shipping_address') {
            $addressId = (int) $this->order->getShippingAddressId();
        }

        if (!$addressId) {
            return;
        }

        $address = $this->address->loadAddress($addressId);
        if ($address->getId()) {
            $this->coreRegistry->register('order_address', $address);
        } else {
            throw new LocalizedException(__('Can not load address'));
        }
    }
}
