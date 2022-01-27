<?php

namespace HookahShisha\Customerb2b\Block\Myaccount;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\AuthorizationInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\CountryInformationProvider;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

/**
 * Company Profile block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Businessdetail extends Template
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var CountryInformationProvider
     */
    private $countryInformationProvider;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CompanyInterface
     */
    private $company = null;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var CustomerInterface
     */
    private $companyAdmin;

    /**
     * @var UserInterface
     */
    private $salesRepresentative;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @inheritdoc
     *
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param CompanyManagementInterface $companyManagement
     * @param CountryInformationProvider $countryInformationProvider
     * @param UserFactory $userFactory
     * @param ManagerInterface $messageManager
     * @param CustomerNameGenerationInterface $customerViewHelper
     * @param AuthorizationInterface $authorization
     * @param \HookahShisha\Customerb2b\Model\Company\Source\Businesstype $businesstype
     * @param \HookahShisha\Customerb2b\Model\Company\Source\AnnualTurnOver $annualTurnOver
     * @param \HookahShisha\Customerb2b\Model\Company\Source\HearAboutUs $hearAboutUs
     * @param \HookahShisha\Customerb2b\Model\Company\Source\NumberOfEmp $numberOfEmp
     * @param array $data
     */
    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        CompanyManagementInterface $companyManagement,
        CountryInformationProvider $countryInformationProvider,
        UserFactory $userFactory,
        ManagerInterface $messageManager,
        CustomerNameGenerationInterface $customerViewHelper,
        AuthorizationInterface $authorization,
        \HookahShisha\Customerb2b\Model\Company\Source\Businesstype $businesstype,
        \HookahShisha\Customerb2b\Model\Company\Source\AnnualTurnOver $annualTurnOver,
        \HookahShisha\Customerb2b\Model\Company\Source\HearAboutUs $hearAboutUs,
        \HookahShisha\Customerb2b\Model\Company\Source\NumberOfEmp $numberOfEmp,
        array $data = []
    ) {
        $data['directoryDataHelper'] = ObjectManager::getInstance()->get(Data::class);
        parent::__construct($context, $data);
        $this->userContext = $userContext;
        $this->companyManagement = $companyManagement;
        $this->countryInformationProvider = $countryInformationProvider;
        $this->userFactory = $userFactory;
        $this->messageManager = $messageManager;
        $this->customerViewHelper = $customerViewHelper;
        $this->authorization = $authorization;
        $this->businesstype = $businesstype;
        $this->annualTurnOver = $annualTurnOver;
        $this->hearAboutUs = $hearAboutUs;
        $this->numberOfEmp = $numberOfEmp;
    }

    /**
     * Retrieve form business type
     *
     * @return mixed
     */
    public function getBusinessType()
    {
        return $this->businesstype->toOptionArray();
    }

    /**
     * Retrieve form AnnualTurnOver
     *
     * @return mixed
     */
    public function getAnnualTurnOver()
    {
        return $this->annualTurnOver->toOptionArray();
    }

    /**
     * Retrieve form HearAboutUs
     *
     * @return mixed
     */
    public function getHearAboutUs()
    {
        return $this->hearAboutUs->toOptionArray();
    }

    /**
     * Retrieve form NumberOfEmp
     *
     * @return mixed
     */
    public function getNumberOfEmp()
    {
        return $this->numberOfEmp->toOptionArray();
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks if account view is allowed.
     *
     * @return bool
     */
    public function isViewAccountAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::view_account');
    }

    /**
     * Checks if account edit is allowed.
     *
     * @return bool
     */
    public function isEditAccountAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_account');
    }

    /**
     * Checks if address view is allowed.
     *
     * @return bool
     */
    public function isViewAddressAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::view_address');
    }

    /**
     * Checks if address edit is allowed.
     *
     * @return bool
     */
    public function isEditAddressAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_address');
    }

    /**
     * Checks if contacts view is allowed.
     *
     * @return bool
     */
    public function isViewContactsAllowed()
    {
        return $this->authorization->isAllowed('Magento_Company::contacts');
    }

    /**
     * Get countries list
     *
     * @return array
     */
    public function getCountriesList()
    {
        return $this->countryInformationProvider->getCountriesList();
    }

    /**
     * Get form messages
     *
     * @return array
     */
    public function getFormMessages()
    {
        $messagesList = [];
        $messagesCollection = $this->messageManager->getMessages(true);

        if ($messagesCollection && $messagesCollection->getCount()) {
            $messages = $messagesCollection->getItems();
            foreach ($messages as $message) {
                $messagesList[] = $message->getText();
            }
        }

        return $messagesList;
    }

    /**
     * Is edit link displayed
     *
     * @return bool
     */
    public function isEditLinkDisplayed()
    {
        return $this->authorization->isAllowed('Magento_Company::edit_account')
        || $this->authorization->isAllowed('Magento_Company::edit_address');
    }

    /**
     * Get current customer's company
     *
     * @return CompanyInterface
     */
    public function getCustomerCompany()
    {
        if ($this->company !== null) {
            return $this->company;
        }

        $customerId = $this->userContext->getUserId();

        if ($customerId) {
            $this->company = $this->companyManagement->getByCustomerId($customerId);
        }

        return $this->company;
    }

    /**
     * Gets company street label
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyStreetLabel(CompanyInterface $company)
    {
        $streetLabel = '';
        $streetData = $company->getStreet();
        $streetLabel .= (!empty($streetData[0])) ? $streetData[0] : '';
        $streetLabel .= (!empty($streetData[1])) ? ' ' . $streetData[1] : '';

        return $streetLabel;
    }

    /**
     * Is company address displayed
     *
     * @param CompanyInterface $company
     * @return bool
     */
    public function isCompanyAddressDisplayed(CompanyInterface $company)
    {
        return $company->getCountryId() ? true : false;
    }

    /**
     * Get company address string
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyAddressString(CompanyInterface $company)
    {
        $addressParts = [];

        $addressParts[] = $company->getCity();
        $addressParts[] = $this->countryInformationProvider->getActualRegionName(
            $company->getCountryId(),
            $company->getRegionId(),
            $company->getRegion()
        );
        $addressParts[] = $company->getPostcode();

        return implode(', ', array_filter($addressParts));
    }

    /**
     * Get company country label
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyCountryLabel(CompanyInterface $company)
    {
        return $this->countryInformationProvider->getCountryNameByCode($company->getCountryId());
    }

    /**
     * Get company admin name
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminName(CompanyInterface $company)
    {
        $companyAdmin = $this->getCompanyAdmin($company);

        return ($companyAdmin && $companyAdmin->getId())
        ? $this->customerViewHelper->getCustomerName($companyAdmin) : '';
    }

    /**
     * Get company admin job title
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminJobTitle(CompanyInterface $company)
    {
        $jobTitle = '';
        $companyAdmin = $this->getCompanyAdmin($company);

        if ($companyAdmin && $companyAdmin->getId()) {
            $extensionAttributes = $companyAdmin->getExtensionAttributes()->getCompanyAttributes();

            if ($extensionAttributes) {
                $jobTitle = $extensionAttributes->getJobTitle();
            }
        }

        return $jobTitle;
    }

    /**
     * Get company admin email
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getCompanyAdminEmail(CompanyInterface $company)
    {
        $companyAdmin = $this->getCompanyAdmin($company);

        return ($companyAdmin && $companyAdmin->getId()) ? $companyAdmin->getEmail() : '';
    }

    /**
     * Get sales representative name
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getSalesRepresentativeName(CompanyInterface $company)
    {
        $salesRepresentative = $this->getSalesRepresentative($company);

        return ($salesRepresentative && $salesRepresentative->getId()) ? $salesRepresentative->getName() : '';
    }

    /**
     * Get sales representative email
     *
     * @param CompanyInterface $company
     * @return string
     */
    public function getSalesRepresentativeEmail(CompanyInterface $company)
    {
        $salesRepresentative = $this->getSalesRepresentative($company);

        return ($salesRepresentative && $salesRepresentative->getId()) ? $salesRepresentative->getEmail() : '';
    }

    /**
     * Get company admin
     *
     * @param CompanyInterface $company
     * @return CustomerInterface
     */
    protected function getCompanyAdmin(CompanyInterface $company)
    {
        if ($this->companyAdmin === null) {
            $this->companyAdmin = $this->companyManagement->getAdminByCompanyId($company->getId());
        }

        return $this->companyAdmin;
    }

    /**
     * Get company sales representative
     *
     * @param CompanyInterface $company
     * @return User
     */
    private function getSalesRepresentative(CompanyInterface $company)
    {
        if ($this->salesRepresentative !== null) {
            return $this->salesRepresentative;
        }

        $salesRepresentativeId = $company->getSalesRepresentativeId();
        if ($salesRepresentativeId) {
            $this->salesRepresentative = $this->userFactory->create()->load($salesRepresentativeId);
        }

        return $this->salesRepresentative;
    }
}
