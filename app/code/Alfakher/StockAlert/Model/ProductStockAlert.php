<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\ProductAlert\Model\Email;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\ProductAlert\Model\EmailFactory;
use Magento\ProductAlert\Model\ProductSalability;
use Alfakher\StockAlert\Logger\Logger;

/**
 * ProductAlert observer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductStockAlert extends \Magento\ProductAlert\Model\Observer
{
    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'catalog/productalert/allow_stock';

    /**
     * Default value of bunch size to load alert items
     */
    private const DEFAULT_BUNCH_SIZE = 10000;

    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;


    /**
     * @var int
     */
    private $bunchSize;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Data $catalogData
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param PriceCollectionFactory $priceColFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DateTimeFactory $dateFactory
     * @param StockCollectionFactory $stockColFactory
     * @param TransportBuilder $transportBuilder
     * @param EmailFactory $emailFactory
     * @param StateInterface $inlineTranslation
     * @param Logger $logger
     * @param ProductSalability|null $productSalability
     * @param int $bunchSize
     */
    public function __construct(
        Data $catalogData,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PriceCollectionFactory $priceColFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        DateTimeFactory $dateFactory,
        StockCollectionFactory $stockColFactory,
        TransportBuilder $transportBuilder,
        EmailFactory $emailFactory,
        StateInterface $inlineTranslation,
        Logger $logger,
        ProductSalability $productSalability = null,
        int $bunchSize = 0
    ){
        $objectManager = ObjectManager::getInstance();
        $this->productSalability = $productSalability ?: $objectManager->get(ProductSalability::class);
        $this->bunchSize = $bunchSize ?: self::DEFAULT_BUNCH_SIZE;
        parent::__construct(
            $catalogData,
            $scopeConfig,
            $storeManager,
            $priceColFactory,
            $customerRepository,
            $productRepository,
            $dateFactory,
            $stockColFactory,
            $transportBuilder,
            $emailFactory,
            $inlineTranslation,
            $productSalability,
            $bunchSize
        );
        $this->logger = $logger;
    }

    /**
     * Process stock emails
     *
     * @param Email $email
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processStock(Email $email)
    {
        $this->logger->info('Stock Process');
        $email->setType('stock');

        foreach ($this->_getWebsites() as $website) {
            /* @var $website Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!$this->_scopeConfig->getValue(
                self::XML_PATH_STOCK_ALLOW,
                ScopeInterface::SCOPE_STORE,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )
            ) {
                continue;
            }
            try {
                $collection = $this->_stockColFactory->create()
                    ->addWebsiteFilter($website->getId())
                    ->addStatusFilter(0)
                    ->setCustomerOrder()
                    ->addOrder('product_id');
            } catch (\Exception $e) {
                $this->logger->info('Error',[
                    'error' => $e->getMessage()
                ]);
                $this->_errors[] = $e->getMessage();
                throw $e;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($this->loadItems($collection, $this->bunchSize) as $alert) {
                $this->setAlertStoreId($alert, $email);
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $customerFactory = $objectManager->create(\Magento\Customer\Model\CustomerFactory::class);
                        $customer = $customerFactory->create()->load($alert->getCustomerId());
                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomerData($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $website->getDefaultStore()->getId()
                    );

                    $product->setCustomerGroupId($customer->getGroupId());

                    if ($this->productSalability->isSalable($product, $website)) {
                        $email->addStockProduct($product);
                        $alert->setSendDate($this->_dateFactory->create()->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                } catch (\Exception $e) {
                    $this->logger->info("Error", [
                        'error' => $e->getMessage()
                    ]);
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (\Exception $e) {
                    $this->logger->info('Error while sending email', [
                        'error' => $e->getMessage()
                    ]);
                    $this->_errors[] = $e->getMessage();
                    throw $e;
                }
            }
        }

        return $this;
    }

    /**
     * Run process send product alerts
     *
     * @return $this
     * @throws \Exception
     */
    public function process()
    {
        $this->logger->info('Process');
        /* @var $email Email */
        $email = $this->_emailFactory->create();
        try {
            $this->_processPrice($email);
            $this->_processStock($email);
            $this->_sendErrorEmail();
        } catch (\Exception $e) {
            $this->logger->info('process()', [
                'error' => $e->getMessage()
            ]);
            $this->_errors[] = $e->getMessage();
            throw $e;
        }
        return $this;
    }

    /**
     * Set alert store id.
     *
     * @param Price|Stock $alert
     * @param Email $email
     */
    private function setAlertStoreId($alert, Email $email)
    {
        $alertStoreId = $alert->getStoreId();
        if ($alertStoreId) {
            $email->setStoreId((int)$alertStoreId);
        }

        return $this;
    }

    /**
     * Load items by bunch size
     *
     * @param AbstractCollection $collection
     * @param int $bunchSize
     * @return \Generator
     */
    private function loadItems(AbstractCollection $collection, int $bunchSize): \Generator
    {
        $collection->setPageSize($bunchSize);
        $pageCount = $collection->getLastPageNumber();
        $curPage = 1;
        while ($curPage <= $pageCount) {
            $collection->clear();
            $collection->setCurPage($curPage);
            foreach ($collection as $item) {
                yield $item;
            }
            $curPage++;
        }
    }
}
