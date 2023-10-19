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
    private CollectionFactory $subscriptionCollectionFactory;
    private stockRegistry $stockRegistry;
    private Logger $logger;
    private Data $helperData;

    /**
     * @param CollectionFactory $subscriptionCollectionFactory
     * @param stockRegistry $stockRegistry
     * @param Logger $logger
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $subscriptionCollectionFactory,
        stockRegistry $stockRegistry,
        Logger $logger,
        Data $helperData
    ) {
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info("Stock Alert Cron Started for Guest");
        $this->processBackInStock();
    }

    /**
     * @return bool
     */
    public function processBackInStock(): bool
    {
        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addFieldToFilter('send_count', 0);
        $subscriptions = $collection->getItems();
        if ($subscriptions) {
            foreach ($subscriptions as $subscription) {
                $productId = (int)$subscription->getData('product_id');
                $this->logger->info("Product Id back in stock", [
                    'product_id' => $productId
                ]);
                if ($this->productIsBackInStock($productId)) {
                    $this->helperData->sendBackInStockEmail(
                        $subscription->getData('email_id'),
                        (int)$subscription->getData('product_id'),
                        $subscription->getData('name')
                    );
                    $subscription->setData('send_date', date('Y-m-d H:i:s'));
                    $subscription->setData('send_count', (int)$subscription->getData('send_count') + 1);
                    $subscription->setData('status', 1);
                    $subscription->save();
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $productId
     */
    private function productIsBackInStock(int $productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getIsInStock();
    }
}
