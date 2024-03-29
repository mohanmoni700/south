<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Translate\Inline\StateInterface as TranslateStateInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilder;
use Alfakher\StockAlert\Logger\Logger;
use Magento\Store\Model\App\Emulation;

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
     * @var Emulation
     */
    private Emulation $emulation;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param TranslateStateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Logger $logger
     * @param EmailData $emailDataHelper
     * @param Emulation $emulation
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        TranslateStateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Logger $logger,
        EmailData $emailDataHelper,
        Emulation $emulation
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->emailDataHelper = $emailDataHelper;
        $this->emulation = $emulation;
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
     * @param $product
     * @param string $customerName
     * @param $storeId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function sendBackInStockEmail($email, $product, $customerName, $storeId)
    {
        try {
            $this->emulation->startEnvironmentEmulation($storeId);

            $store = $this->storeManager->getStore($storeId);
            $alertGrid = $this->emailDataHelper->getAlertGrid($store, $product);

            $templateVars = [
                'customerName' => $customerName,
                'alertGrid' => $alertGrid,
            ];

            $sender = $this->emailDataHelper->getSender($storeId);

            $from = ['email' => $sender, 'name' => $sender];

            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $templateId = $this->emailDataHelper->getTemplateId($storeId);

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope($from)
                ->addTo($email, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->emulation->stopEnvironmentEmulation();
            $this->inlineTranslation->resume();
            return $this;
        } catch (MailException | LocalizedException $e) {
            $this->logger->info("Error in email data", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
