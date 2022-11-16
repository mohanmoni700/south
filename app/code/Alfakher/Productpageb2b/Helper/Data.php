<?php
declare (strict_types = 1);

namespace Alfakher\Productpageb2b\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Company\Api\CompanyManagementInterface;
use \Magento\CatalogInventory\Api\Data\StockItemInterface;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use \Magento\CatalogInventory\Api\StockStateInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Customer\Model\Context as CustomerContext;
use \Magento\Customer\Model\CustomerFactory;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Http\Context;
use \Magento\Framework\HTTP\Header;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @var CompanyManagementInterface
     */
    protected $companyRepository;

    /**
     * @var Header
     */
    protected $httpHeader;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var StockRegistryInterface|null
     */
    protected $stockRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StockStateInterface
     */
    protected $stockInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $proRepo;

    /**
     * Constructor for data
     *
     * @param Session $session
     * @param CollectionFactory $collection
     * @param Context $httpContext
     * @param CompanyManagementInterface $companyRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Header $httpHeader
     * @param CustomerFactory $customerFactory
     * @param StockStateInterface $stockInterface
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $proRepo
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Session $session,
        CollectionFactory $collection,
        Context $httpContext,
        CompanyManagementInterface $companyRepository,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        Header $httpHeader,
        CustomerFactory $customerFactory,
        StockStateInterface $stockInterface,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $proRepo,
        StockRegistryInterface $stockRegistry
    ) {
        $this->collection = $collection;
        $this->_customerSession = $session;
        $this->httpContext = $httpContext;
        $this->companyRepository = $companyRepository;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->httpHeader = $httpHeader;
        $this->customerFactory = $customerFactory;
        $this->_stockInterface = $stockInterface;
        $this->storeManager = $storeManager;
        $this->proRepo = $proRepo;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Get Document status
     *
     * @return int
     */
    public function getDocMessageData()
    {
        return $this->httpContext->getValue('document_status');
    }

    /**
     * Get Document is expired or not
     *
     * @return int
     */
    public function getExpiryMsg()
    {
        return $this->httpContext->getValue('document_expiry_date');
    }

    /**
     * Get is Document uploaded or not
     *
     * @return int
     */
    public function getDocuments()
    {
        return $this->httpContext->getValue('is_document_upload');
    }

    /**
     * Get configuration value
     *
     * @param string $section
     * @return mixed
     */
    public function getConfigValue($section)
    {
        return $this->scopeConfig->getValue($section, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get is Document uploaded or not
     *
     * @return int
     */
    public function isMobileDevice()
    {
        return $this->httpContext->getValue('is_mobiledevice');
    }

    /**
     * Get GrossMargin value
     *
     * @param int $productId
     * @return float
     */
    public function getStockQty($productId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        return $this->_stockInterface->getStockQty($productId, $websiteId);
    }

    /**
     * Get is Finance Verified value
     *
     * @return int
     */
    public function getAdminCustomer()
    {
        return $this->_customerSession->getLoggedAsCustomerAdmindId();
    }

    /**
     * Get GrossMargin value
     *
     * @param int $productId
     * @return float
     */
    public function getGrossMargin($productId)
    {
        return $this->proRepo->getById($productId)->getCost();
    }

    /**
     * Get is Finance Verified value
     *
     * @return int
     */
    public function getIsFinanceVerified()
    {
        $customerId = $this->httpContext->getValue('customer_id');
        $customerData = $this->customerFactory->create()->load($customerId);
        return $customerData->getIsfinanceVerified();
    }

    /**
     * Get configuration value
     *
     * @param string $section
     * @param int $websiteid
     * @return mixed
     */
    public function getGrossStatus($section, $websiteid)
    {
        return $this->scopeConfig->getValue($section, ScopeInterface::SCOPE_STORE, $websiteid);
    }

    /**
     * Get stock status for given product
     *
     * @param int $productId
     * @return bool
     */
    public function getStockStatus($productId)
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($productId);
        $isInStock = $stockItem ? $stockItem->getIsInStock() : false;
        return $isInStock;
    }
}
