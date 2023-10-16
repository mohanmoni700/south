<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Observer;

use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser\CollectionFactory as CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface as stockRegistry;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as TranslateStateInterface;

class ProcessBackInStock
{
    private CollectionFactory $subscriptionCollectionFactory;
    private stockRegistry $stockRegistry;
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private TransportBuilder $transportBuilder;
    private TranslateStateInterface $inlineTranslation;

    public function __construct(
        CollectionFactory $subscriptionCollectionFactory,
        stockRegistry $stockRegistry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        TranslateStateInterface $inlineTranslation
    ) {
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addFieldToFilter('send_count', 0);
        $subscriptions = $collection->getItems();
        $this->processBackInStock($subscriptions);
    }

    private function processBackInStock($subscriptions): bool
    {
        foreach ($subscriptions as $subscription) {
            $productId = $subscription->getProductId();
            if ($this->productIsBackInStock($productId)) {
                $this->sendBackInStockEmail($subscription->getEmail(), $subscription->getProductName());
                $subscription->setSendDate(date('Y-m-d H:i:s'));
                $subscription->setSendCount($subscription->getSendCount() + 1);
                $subscription->setStatus(1);
                $subscription->save();
            }
        }
        return true;
    }

    private function productIsBackInStock(mixed $productId): int
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getIsInStock();
    }

    private function sendBackInStockEmail($email, $productName)
    {
        $templateVars = [
            'productName' => $productName
        ];

        $storeId = $this->storeManager->getStore()->getId();

        $sender =  $this->scopeConfig->getValue(
            "catalog/productalert/email_identity",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $from = ['email' => $sender, 'name' => $sender];
        $to = [$email];

        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $storeId
        ];

        $templateId = $this->scopeConfig->getValue(
            "catalog/productalert/guest_user_email_template",
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }
}
