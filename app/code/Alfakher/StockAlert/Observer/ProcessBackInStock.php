<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Observer;

use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser\CollectionFactory as CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface as stockRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as TranslateStateInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Alfakher\StockAlert\Logger\Logger;

class ProcessBackInStock
{
    private CollectionFactory $subscriptionCollectionFactory;
    private stockRegistry $stockRegistry;
    private StoreManagerInterface $storeManager;
    private ScopeConfigInterface $scopeConfig;
    private TransportBuilder $transportBuilder;
    private TranslateStateInterface $inlineTranslation;
    private ProductRepositoryInterface $productRepository;
    private Logger $logger;

    /**
     * @param CollectionFactory $subscriptionCollectionFactory
     * @param stockRegistry $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param TranslateStateInterface $inlineTranslation
     * @param ProductRepositoryInterface $productRepository
     * @param Logger $logger
     */
    public function __construct(
        CollectionFactory $subscriptionCollectionFactory,
        stockRegistry $stockRegistry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        TranslateStateInterface $inlineTranslation,
        ProductRepositoryInterface $productRepository,
        Logger $logger
    ) {
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info("Stock Alert Cron Started for Guest");
        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addFieldToFilter('send_count', 0);
        $subscriptions = $collection->getItems();
        $this->processBackInStock($subscriptions);
    }

    /**
     * @param $subscriptions
     * @return bool
     */
    public function processBackInStock($subscriptions): bool
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
                    $this->sendBackInStockEmail(
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

    /**
     * @param $email
     * @param int $productId
     * @return $this
     * @throws NoSuchEntityException
     */
    private function sendBackInStockEmail($email, int $productId, $customerName)
    {
        try {
            $product = $this->productRepository->getById($productId);

            $templateVars = [
                'customerName' => $customerName,
                'productName' => $product->getName()
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $from = ['email' => $fromEmail, 'name' => $fromName];
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

            $this->logger->info("Email Data", [
                'customer_name' => $customerName,
                'product_name' => $product->getName(),
                'email' => $email,
                'from_email' => $from,
                'to_email' => $to,
                'template_id' => $templateId,
                'store_id' => $storeId
            ]);
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();

            $transport->sendMessage();
            $this->logger->info("Email sent successfully");

            $this->inlineTranslation->resume();
            return $this;
        } catch (NoSuchEntityException $e) {
            $this->logger->info("Error in product",[
                'product_id' => $productId
            ]);
            throw new NoSuchEntityException(__("Requested product does not exist - %1", $productId));
        } catch (MailException|LocalizedException $e) {
            $this->logger->info("Error in email data",[
                'error' => $e->getMessage()
            ]);
        }
    }
}
