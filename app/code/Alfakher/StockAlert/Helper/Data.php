<?php

namespace Alfakher\StockAlert\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface as TranslateStateInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilder;
use Alfakher\StockAlert\Logger\Logger;

class Data
{
    public const XML_PATH_EMAIL_STOCK_TEMPLATE = 'catalog/productalert/guest_user_email_template';
    public const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_general/email';

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
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

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
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TranslateStateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig,
        TranslateStateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Logger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * Get store ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Website ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId(): int
    {
        return $this->storeManager->getStore()->getWebsiteId();
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
     * @param $email
     * @param int $productId
     * @param $customerName
     * @return $this
     * @throws NoSuchEntityException
     */
    public function sendBackInStockEmail($email, int $productId, $customerName)
    {
        try {
            $product = $this->productRepository->getById($productId);

            $templateVars = [
                'customerName' => $customerName,
                'productName' => $product->getName()
            ];

            $storeId = $this->storeManager->getStore()->getId();
            $sender = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
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
                self::XML_PATH_EMAIL_STOCK_TEMPLATE,
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
