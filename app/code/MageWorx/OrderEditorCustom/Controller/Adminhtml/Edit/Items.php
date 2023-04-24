<?php
namespace MageWorx\OrderEditorCustom\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteDataBackupRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Controller\Adminhtml\AbstractAction;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Class Edit Order Items
 */
class Items extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Items
{
    /**
     * @var QuoteDataBackupRepositoryInterface
     */
    private $backupRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * Items constructor.
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
     * @param ProductRepositoryInterface $productRepository
     * @param ManagerInterface $messageManager
     * @param StockRegistryInterface $stockRegistry
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
        QuoteDataBackupRepositoryInterface $backupRepository,
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager,
        StockRegistryInterface $stockRegistry
    ) {
        $this->backupRepository = $backupRepository;
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
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Update
     *
     * @return void
     */
    protected function update()
    {
        $this->updateOrderItems();
        try {
            $quoteBackup = $this->backupRepository->getByQuoteId($this->getOrder()->getQuoteId());
            $this->backupRepository->delete($quoteBackup);
        } catch (NoSuchEntityException $e) {
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @return string|void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function updateOrderItems()
    {
        $params = $this->getRequest()->getParams();

        $checkStock = true;
        foreach ($params['item'] as $value) {
            if (isset($value['action']) && $value['action'] != 'remove'):
                $productStockData = $this->stockRegistry->getStockItem($value['product_id']);
                $productId = $value['product_id'];
                $productOrderQty = $value['fact_qty'];
                $productQty = $productStockData->getQty();
                $product = $this->productRepository->getById($productId);
                if ($product->getTypeId() != 'bundle' &&
                    $product->getTypeId() != 'configurable'
                    && ($productQty < $productOrderQty)) {
                        $this->messageManager->addError(__(
                            'Product "'.$product->getName().'" That you are trying to add is not available'
                        ));
                        $checkStock = false;
                }
            endif;
        }
        if (!$checkStock) {
            return $this->prepareResponse();
        }
        $order = $this->getOrder();
        $order->editItems($params);
    }

    /**
     * PrepareResponse
     *
     * @return string
     */
    protected function prepareResponse(): string
    {
        return static::ACTION_RELOAD_PAGE;
    }
}
