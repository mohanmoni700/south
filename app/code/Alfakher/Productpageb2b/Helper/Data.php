<?php
namespace Alfakher\Productpageb2b\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Company\Api\CompanyManagementInterface;
use \Magento\Customer\Model\Context as CustomerContext;
use \Magento\Customer\Model\CustomerFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext;

	/**
	 * @var Magento\Framework\HTTP\Header
	 */
	protected $httpHeader;

	/**
	 * @var Magento\Customer\Model\CustomerFactory
	 */
	protected $customerFactory;

	/**
	 * @param \Magento\Customer\Model\Session $session
	 * @param CollectionFactory $collection
	 * @param \Magento\Framework\App\Http\Context $httpContext
	 * @param CompanyManagementInterface $companyRepository
	 * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Framework\HTTP\Header $httpHeader
	 * @param CustomerFactory $customerFactory
	 */
	public function __construct(
		\Magento\Customer\Model\Session $session,
		CollectionFactory $collection,
		\Magento\Framework\App\Http\Context $httpContext,
		CompanyManagementInterface $companyRepository,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\HTTP\Header $httpHeader,
		CustomerFactory $customerFactory
	) {
		$this->collection = $collection;
		$this->_customerSession = $session;
		$this->httpContext = $httpContext;
		$this->companyRepository = $companyRepository;
		$this->customerRepository = $customerRepository;
		$this->scopeConfig = $scopeConfig;
		$this->httpHeader = $httpHeader;
		$this->customerFactory = $customerFactory;
	}

	/**
	 * Checking customer login status
	 *
	 * @return bool
	 */
	public function isCustomerLoggedIn() {
		return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
	}

	/**
	 * @inheritDoc
	 */
	public function getDocMessageData() {
		return $this->httpContext->getValue('document_status');
	}

	/**
	 * @inheritDoc
	 */
	public function getExpiryMsg() {
		return $this->httpContext->getValue('document_expiry_date');
	}

	/**
	 * @inheritDoc
	 */
	public function getDocuments() {
		return $this->httpContext->getValue('is_document_upload');
	}

	/**
	 * @inheritDoc
	 */
	public function getConfigValue($section) {
		return $this->scopeConfig->getValue($section, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	/**
	 * @inheritDoc
	 */
	public function isMobileDevice() {
		return $this->httpContext->getValue('is_mobiledevice');
	}

	/**
	 * @inheritDoc
	 */
	public function getMigrateDocument() {
		$customerId = $this->httpContext->getValue('customer_id');
		$customerData = $this->customerFactory->create()->load($customerId);
		$migrateCustomerDocument = $customerData->getIsMigrationDocuments();
		return (int) $migrateCustomerDocument;
	}
}
