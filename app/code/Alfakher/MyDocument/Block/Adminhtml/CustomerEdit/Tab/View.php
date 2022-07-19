<?php
namespace Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Customer\Source\CustomerType as CustomerTypeSource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;

class View extends Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var _template
     */
    protected $_template = 'tab/customer_view.phtml';

    /**
     * @var CompanyCustomerInterface
     */
    private $customerAttributes;

    /**
     * @var \HookahShisha\Customerb2b\Helper\Data
     */
    private $helperData;

    /**
     * @var \Alfakher\MyDocument\Helper\Data
     */
    protected $documentHelperData;

    /**
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param DirectoryList $directory
     * @param CustomerFactory $customer
     * @param CollectionFactory $collection
     * @param AddressFactory $address
     * @param FormKey $formKey
     * @param CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     * @param CustomerTypeSource $customerTypeSource
     * @param \HookahShisha\Customerb2b\Helper\Data $helperData
     * @param \Alfakher\MyDocument\Helper\Data $documentHelperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        Context $context,
        \Magento\Framework\Registry $registry,
        DirectoryList $directory,
        CustomerFactory $customer,
        CollectionFactory $collection,
        AddressFactory $address,
        FormKey $formKey,
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        CustomerTypeSource $customerTypeSource,
        \HookahShisha\Customerb2b\Helper\Data $helperData,
        \Alfakher\MyDocument\Helper\Data $documentHelperData,
        array $data = []
    ) {
        $this->fileFactory = $fileFactory;
        $this->_directory = $directory;
        $this->_coreRegistry = $registry;
        $this->customer = $customer;
        $this->collection = $collection;
        $this->address = $address;
        $this->formKey = $formKey;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerTypeSource = $customerTypeSource;
        $this->helperData = $helperData;
        $this->documentHelperData = $documentHelperData;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        if ($this->documentHelperData->isCustomerFromUsa($this->getCustomer())) {
            $this->addChild(
                'add_documents',
                \Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab\AddDocuments::class,
                ['template' => 'Alfakher_MyDocument::tab/add_documents_usa.phtml']
            );
        } else {
            $this->addChild(
                'add_documents',
                \Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab\AddDocuments::class,
                ['template' => 'Alfakher_MyDocument::tab/add_documents_nonusa.phtml']
            );
        }
        if (!empty($this->getDocumentCollection())) {
            $this->addChild(
                'add_more_doc',
                \Alfakher\MyDocument\Block\Adminhtml\CustomerEdit\Tab\AddDocuments::class,
                ['template' => 'Alfakher_MyDocument::tab/add_more_doc.phtml']
            );

        }
    }

    /**
     * @inheritDoc
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getCustomer()
    {
        return $this->customer->create()->load($this->getCustomerId());
    }

    /**
     * @inheritDoc
     */
    public function getTabLabel()
    {
        return __('Documents');
    }

    /**
     * @inheritDoc
     */
    public function getTabTitle()
    {
        return __('Documents');
    }

    /**
     * @inheritDoc
     */
    public function getCustomercollection($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        return $customer;
    }

    /**
     * @inheritDoc
     */

    public function getCustomeraddress($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->address->create()->load($billingAddressId);
        return $billingAddress;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection()
    {
        $collection = $this->collection->create()
            ->addFieldToFilter('customer_id', ['eq' => $this->_coreRegistry->registry('current_customer_id')]);
        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Retrieve customer extension attributes.
     *
     * @return CompanyCustomerInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAttributes()
    {
        if (!$this->customerAttributes) {
            if (isset($this->_backendSession->getCustomerData()['account']['id'])) {
                $customer = $this->_backendSession->getCustomerData()['account'];
                $this->customerAttributes = $this->customerRepository->getById($customer['id'])
                    ->getExtensionAttributes()->getCompanyAttributes();
            }
        }
        return $this->customerAttributes;
    }

    /**
     * Retrieve company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        $companyName = '';
        if ($this->getCompany()) {
            $companyName = $this->getCompany()->getCompanyName();
        }
        return $companyName;
    }
    /**
     * Retrieve company id.
     *
     * @return string
     */
    public function getEntityId()
    {
        $companyName = '';
        if ($this->getCompany()) {
            $companyId = $this->getCompany()->getEntityId();
        }
        return $companyId;
    }

    /**
     * Get company.
     *
     * @return CompanyInterface|null
     */
    public function getCompany()
    {
        $company = null;
        if ($this->getCustomerAttributes()) {
            $companyId = $this->getCustomerAttributes()->getCompanyId();
            try {
                $company = $this->companyRepository->get($companyId);
            } catch (NoSuchEntityException $e) {
                $company = null;
            }
        }
        return $company;
    }

    /**
     * Get vatTaxId.
     *
     * @return CompanyInterface|null
     */
    public function getVatTaxId()
    {
        $vatTaxId = '';
        if ($this->getCompany()) {
            $vatTaxId = $this->getCompany()->getVatTaxId();
        }
        return $vatTaxId;
    }

    /**
     * Get resellerId.
     *
     * @return CompanyInterface|null
     */
    public function getResellerId()
    {
        $resellerId = '';
        if ($this->getCompany()) {
            $resellerId = $this->getCompany()->getResellerId();
        }
        return $resellerId;
    }

    /**
     * Get numberOfEmp.
     *
     * @return CompanyInterface|null
     */
    public function getNumberOfEmp()
    {
        $numberOfEmp = '';
        if ($this->getCompany()) {
            // $this->getCompany()->getNumberOfEmp();
            $numberOfEmp = $this->helperData->getEmployees((int) $this->getCompany()->getNumberOfEmp());
        }
        return $numberOfEmp;
    }

    /**
     * Get tinNumber.
     *
     * @return CompanyInterface|null
     */
    public function getTinNumber()
    {
        $tinNumber = '';
        if ($this->getCompany()) {
            $tinNumber = $this->getCompany()->getTinNumber();
        }
        return $tinNumber;
    }

    /**
     * Get tobaccoPermitNumber.
     *
     * @return CompanyInterface|null
     */
    public function getTobaccoPermitNumber()
    {
        $tobaccoPermitNumber = '';
        if ($this->getCompany()) {
            $tobaccoPermitNumber = $this->getCompany()->getTobaccoPermitNumber();
        }
        return $tobaccoPermitNumber;
    }

    /**
     * @inheritDoc
     */
    public function getMediaUrl()
    {
        return $this->documentHelperData->getMediaUrl();
    }

    /**
     * @inheritDoc
     */
    public function checkExtension($file)
    {
        return $this->documentHelperData->checkExtension($file);
    }
}
