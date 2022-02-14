<?php
namespace Alfakher\Productpageb2b\Helper;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Company\Api\CompanyManagementInterface;
use \Magento\Customer\Model\Context as CustomerContext;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * @param CollectionFactory $collection
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CompanyManagementInterface $companyRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        CollectionFactory $collection,
        \Magento\Framework\App\Http\Context $httpContext,
        CompanyManagementInterface $companyRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->collection = $collection;
        $this->_customerSession = $session;
        $this->httpContext = $httpContext;
        $this->companyRepository = $companyRepository;
        $this->customerRepository = $customerRepository;
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
    }

    /**
     * @inheritDoc
     */
    public function getExpiryMsg()
    {
        return $this->httpContext->getValue('document_expiry_date');
    }

    /**
     * @inheritDoc
     */
    public function getDocuments()
    {
        return $this->httpContext->getValue('is_document_upload');
    }
}
