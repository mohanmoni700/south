<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Translate\Inline\StateInterface as TranslateStateInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilder;
use Alfakher\StockAlert\Logger\Logger;

class Data
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var TranslateStateInterface
     */
    private TranslateStateInterface $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var EmailData
     */
    private EmailData $emailDataHelper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param TranslateStateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Logger $logger
     * @param EmailData $emailDataHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        TranslateStateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Logger $logger,
        EmailData $emailDataHelper
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->emailDataHelper = $emailDataHelper;
    }

    /**
     * Get store ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * Get Website ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId(): int
    {
        return (int)$this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get Product ID
     *
     * @return mixed
     */
    public function getProductId(): ?string
    {
        return $this->request->getParam('product_id');
    }

    /**
     * Send back in stock email
     *
     * @param string $email
     * @param int $productId
     * @param string $customerName
     * @return $this
     * @throws NoSuchEntityException
     */
    public function sendBackInStockEmail($email, int $productId, $customerName)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $storeId = $this->storeManager->getStore()->getId();
            $store = $this->storeManager->getStore($storeId);
            $alertGrid = $this->emailDataHelper->getAlertGrid($store, $product);

            $templateVars = [
                'customerName' => $customerName,
                'alertGrid' => $alertGrid,
            ];

            $sender = $this->emailDataHelper->getSender($storeId);

            $from = ['email' => $sender, 'name' => $sender];
            $to = [$email];

            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $templateId = $this->emailDataHelper->getTemplateId($storeId);

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
                ->setFromByScope($from)
                ->addTo($to)
                ->getTransport();

            $transport->sendMessage();
            $this->logger->info("Email sent successfully");

            $this->inlineTranslation->resume();
            return $this;
        } catch (NoSuchEntityException $e) {
            $this->logger->info("Error in product", [
                'product_id' => $productId
            ]);
            throw new NoSuchEntityException(__("Requested product does not exist - %1", $productId));
        } catch (MailException|LocalizedException $e) {
            $this->logger->info("Error in email data", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
