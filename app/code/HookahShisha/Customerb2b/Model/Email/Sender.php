<?php
namespace HookahShisha\Customerb2b\Model\Email;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Model\Config\EmailTemplate as EmailTemplateConfig;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Company\Model\Email\Transporter;
use Magento\Company\Model\Email\CustomerData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Company\Model\Email\Sender as MainSender;

/**
 * Sending company related emails.
 */
class Sender extends MainSender
{
    /**
     * Prefix stores
     */
    public const WEBSITE_COMPANY_MAIL = 'hookahshisha/company_assign/company_websites';

    /**
     * Email template for identity.
     */
    private $xmlPathRegisterEmailIdentity = 'customer/create_account/email_identity';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Transporter
     */
    private $transporter;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var EmailTemplate
     */
    private $emailTemplateConfig;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Transporter $transporter
     * @param CustomerNameGenerationInterface $customerViewHelper
     * @param CustomerData $customerData
     * @param EmailTemplateConfig $emailTemplateConfig
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Transporter $transporter,
        CustomerNameGenerationInterface $customerViewHelper,
        CustomerData $customerData,
        EmailTemplateConfig $emailTemplateConfig,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transporter = $transporter;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerData = $customerData;
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->companyRepository = $companyRepository;
        parent::__construct(
            $storeManager,
            $scopeConfig,
            $transporter,
            $customerViewHelper,
            $customerData,
            $emailTemplateConfig,
            $companyRepository
        );
    }

    /**
     * Send email to customer after assign company to him.
     *
     * @param CustomerInterface $customer
     * @param int $companyId
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendCustomerCompanyAssignNotificationEmail(
        CustomerInterface $customer,
        $companyId
    ) {
        $websiteDetails = $this->scopeConfig->getValue(self::WEBSITE_COMPANY_MAIL);
        $websiteIds = $websiteDetails ? explode(',', $websiteDetails) : [];

        $customerName = $this->customerViewHelper->getCustomerName($customer);
        $companySuperUser = $this->customerData->getDataObjectSuperUser($companyId);
        $mergedCustomerData = $this->customerData->getDataObjectByCustomer($customer, $companyId);

        if ($companySuperUser && $mergedCustomerData) {
            $mergedCustomerData->setData('companyAdminEmail', $companySuperUser->getEmail());
            
            if (in_array($customer->getWebsiteId(), $websiteIds)) {
                $this->sendEmailTemplate(
                    $customer->getEmail(),
                    $customerName,
                    $this->emailTemplateConfig->getCompanyCustomerAssignUserTemplateId(
                        ScopeInterface::SCOPE_STORE,
                        $customer->getStoreId()
                    ),
                    $this->xmlPathRegisterEmailIdentity,
                    ['customer' => $mergedCustomerData],
                    $customer->getStoreId()
                );
            }
        }
        return $this;
    }

    /**
     * Send corresponding email template.
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param string $templateId
     * @param string|array $sender configuration path of email identity
     * @param array $templateParams [optional]
     * @param int|null $storeId [optional]
     * @param array $bcc [optional]
     * @return void
     */
    private function sendEmailTemplate(
        $customerEmail,
        $customerName,
        $templateId,
        $sender,
        array $templateParams = [],
        $storeId = null,
        array $bcc = []
    ) {
        $from = $sender;
        if (is_string($sender)) {
            $from = $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId);
        }
        $this->transporter->sendMessage(
            $customerEmail,
            $customerName,
            $from,
            $templateId,
            $templateParams,
            $storeId,
            $bcc
        );
    }
}
