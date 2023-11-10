<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Observer;

use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser\CollectionFactory as CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface as stockRegistry;
use Magento\Framework\Event\Observer;
use Alfakher\StockAlert\Logger\Logger;
use Alfakher\StockAlert\Helper\Data;

class ProcessBackInStock
{
    public const PAGE_SIZE = '50';

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $stockAlertCollection;

    /**
     * @var stockRegistry
     */
    private stockRegistry $stockRegistry;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @param CollectionFactory $stockAlertCollection
     * @param stockRegistry $stockRegistry
     * @param Logger $logger
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $stockAlertCollection,
        stockRegistry $stockRegistry,
        Logger $logger,
        Data $helperData
    ) {
        $this->stockAlertCollection = $stockAlertCollection;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Observer for processing back in stock
     *
     * @param Observer $observer
     * @return void
     */
    public function execute()
    {
        $this->logger->info("Stock Alert Cron Started for Guest");
        $this->processBackInStock();
    }

    /**
     * Function to process back in stock and send email
     *
     * @return bool
     */
    public function processBackInStock(): bool
    {
        $collection = $this->stockAlertCollection->create();
        $collection->addFieldToFilter('send_count', 0);
        $collection->setPageSize(self::PAGE_SIZE);
        $stockAlertProductData = $collection->getItems();
        if ($stockAlertProductData) {
            foreach ($stockAlertProductData as $stockAlertProduct) {
                $productId = (int)$stockAlertProduct->getData('product_id');
                $this->logger->info("Product Id back in stock", [
                    'product_id' => $productId
                ]);
                if ($this->productIsBackInStock($productId)) {
                    $this->helperData->sendBackInStockEmail(
                        $stockAlertProduct->getData('email_id'),
                        (int)$stockAlertProduct->getData('product_id'),
                        $stockAlertProduct->getData('name'),
                        $stockAlertProduct->getData('store_id')
                    );
                    $stockAlertProduct->setData('send_date', date('Y-m-d H:i:s'));
                    $stockAlertProduct->setData('send_count', (int)$stockAlertProduct->getData('send_count') + 1);
                    $stockAlertProduct->setData('status', 1);
                    $stockAlertProduct->save();
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to check weather product is back in stock
     *
     * @param int $productId
     * @return bool|int
     */
    private function productIsBackInStock(int $productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getIsInStock();
    }
}
