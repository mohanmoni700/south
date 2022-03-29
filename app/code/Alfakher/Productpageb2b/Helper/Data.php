<?php
namespace Alfakher\Productpageb2b\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Company\Api\CompanyManagementInterface;
use \Magento\Customer\Model\Context as CustomerContext;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * @param \Magento\Customer\Model\Session $session
     * @param \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $collection
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Company\Api\CompanyManagementInterface $companyRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        CollectionFactory $collection,
        \Magento\Framework\App\Http\Context $httpContext,
        CompanyManagementInterface $companyRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->collection = $collection;
        $this->_customerSession = $session;
        $this->httpContext = $httpContext;
        $this->companyRepository = $companyRepository;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
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
     * @inheritDoc
     */
    public function getDocMessageData()
    {
        return $this->httpContext->getValue('document_status');
        //return $document_status;
    }
    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function isMobile()
    {
        $userAgent = $this->httpContext->getHttpUserAgent();
        $isMobile = \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
        return $isMobile;
    }
    /**
     * @inheritDoc
     */
    public function getExpiryMsg()
    {
        return $this->httpContext->getValue('document_expiry_date');
        //return $document_expiry_date;
    }
    /**
     * @inheritDoc
     */
    public function getDocuments()
    {
        return $this->httpContext->getValue('is_document_upload');
        //return $document_expiry_date;
    }
    /**
     * @inheritDoc
     */
    public function getConfigValue($section)
    {
        return $this->scopeConfig->getValue($section, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
