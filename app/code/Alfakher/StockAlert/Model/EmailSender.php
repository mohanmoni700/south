<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\ProductAlert\Block\Email\AbstractEmail;
use Magento\ProductAlert\Block\Email\Price;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\ProductAlert\Helper\Data;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Alfakher\StockAlert\Logger\Logger;

/**
 * ProductAlert Email processor
 */
class EmailSender extends \Magento\ProductAlert\Model\Email
{
    const XML_PATH_EMAIL_PRICE_TEMPLATE = 'catalog/productalert/email_price_template';

    const XML_PATH_EMAIL_STOCK_TEMPLATE = 'catalog/productalert/email_stock_template';

    const XML_PATH_EMAIL_IDENTITY = 'catalog/productalert/email_identity';

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $productAlertData
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param View $customerHelper
     * @param Emulation $appEmulation
     * @param TransportBuilder $transportBuilder
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $productAlertData,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        View $customerHelper,
        Emulation $appEmulation,
        TransportBuilder $transportBuilder,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $productAlertData,
            $scopeConfig,
            $storeManager,
            $customerRepository,
            $customerHelper,
            $appEmulation,
            $transportBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->logger = $logger;
    }

    /**
     * Send customer email
     *
     * @return bool
     * @throws MailException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function send()
    {
        try {
            $this->logger->info('In EmailSender');
            if ($this->_website === null || $this->_customer === null || !$this->isExistDefaultStore()) {
                return false;
            }

            $products = $this->getProducts();
            $templateConfigPath = $this->getTemplateConfigPath();
            if (!in_array($this->_type, ['price', 'stock']) || count($products) === 0 || !$templateConfigPath) {
                return false;
            }

            $storeId = (int) $this->getStoreId() ?: (int) $this->_customer->getStoreId();
            $store = $this->getStore($storeId);

            $this->_appEmulation->startEnvironmentEmulation($storeId);

            $block = $this->getBlock();
            $block->setStore($store)->reset();

            // Add products to the block
            foreach ($products as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $block->addProduct($product);
            }

            $templateId = $this->_scopeConfig->getValue(
                $templateConfigPath,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $alertGrid = $this->_appState->emulateAreaCode(
                Area::AREA_FRONTEND,
                [$block, 'toHtml']
            );
            $this->_appEmulation->stopEnvironmentEmulation();

            $name = $this->_customer->getPrefix() . ' ';
            $name .= $this->_customer->getFirstname() . ' ';
            $name .= $this->_customer->getMiddlename() . ' ';
            $name .= $this->_customer->getLastname() . ' ';
            $name .= $this->_customer->getSuffix();

            $customerName = $name;
            $email = $this->_customer->getEmail();

            $sender = $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $from = ['email' => $sender, 'name' => $sender];
            $this->logger->info('Customer data',[
                'customer_name' => $customerName,
                'email' => $email,
                'sender' => $from
            ]);
            $this->_transportBuilder->setTemplateIdentifier(
                $templateId)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars([
                    'customerName' => $customerName,
                    'alertGrid' => $alertGrid,
                ])
                ->setFromByScope($from)
                ->addTo($email, $customerName)
                ->getTransport()->sendMessage();
            $this->logger->info('Success');
        } catch (\Exception $e) {
            $this->logger->info('Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        return true;
    }

    /**
     * Retrieve the store for the email
     *
     * @param int $storeId
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(int $storeId): StoreInterface
    {
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve the block for the email based on type
     *
     * @return Price|Stock
     * @throws LocalizedException
     */
    private function getBlock(): AbstractEmail
    {
        return $this->_type === 'price'
            ? $this->_getPriceBlock()
            : $this->_getStockBlock();
    }

    /**
     * Retrieve the products for the email based on type
     *
     * @return array
     */
    private function getProducts(): array
    {
        return $this->_type === 'price'
            ? $this->_priceProducts
            : $this->_stockProducts;
    }

    /**
     * Retrieve template config path based on type
     *
     * @return string
     */
    private function getTemplateConfigPath(): string
    {
        return $this->_type === 'price'
            ? self::XML_PATH_EMAIL_PRICE_TEMPLATE
            : self::XML_PATH_EMAIL_STOCK_TEMPLATE;
    }

    /**
     * Check if exists default store.
     *
     * @return bool
     */
    private function isExistDefaultStore(): bool
    {
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }
        return true;
    }
}
