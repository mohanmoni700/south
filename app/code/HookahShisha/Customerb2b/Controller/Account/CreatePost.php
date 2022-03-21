<?php

declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Controller\Account;

use Laminas\Validator\EmailAddress;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\InputException;

/**
 * Create company account action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class CreatePost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var string
     */
    private $formId = 'company_create';

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Company\Model\Action\Validator\Captcha
     */
    private $captchaValidator;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\Create\Session
     */
    private $companyCreateSession;

    /**
     * @var \Magento\Company\Model\CompanyUser|null
     */
    private $companyUser;

    /**
     * @var \HookahShisha\Customerb2b\Helper\Data
     */
    private $helperdata;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var EmailAddress
     */
    private $emailValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Api\DataObjectHelper $objectHelper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Company\Model\Action\Validator\Captcha $captchaValidator
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Company\Model\Create\Session $companyCreateSession
     * @param \Magento\Customer\Model\CustomerExtractor $customerExtractor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \HookahShisha\Customerb2b\Helper\Data $helperdata
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Company\Model\CompanyUser|null $companyUser
     * @param EmailAddress $emailValidator
     * @param \Magento\LoginAsCustomerAssistance\Model\ResourceModel\SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Company\Model\Action\Validator\Captcha $captchaValidator,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Company\Model\Create\Session $companyCreateSession,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \HookahShisha\Customerb2b\Helper\Data $helperdata,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Company\Model\CompanyUser $companyUser = null,
        EmailAddress $emailValidator,
        \Magento\LoginAsCustomerAssistance\Model\ResourceModel\
        SaveLoginAsCustomerAssistanceAllowed $saveLoginAsCustomerAssistanceAllowed
    ) {
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->objectHelper = $objectHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->captchaValidator = $captchaValidator;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerDataFactory = $customerDataFactory;
        $this->companyCreateSession = $companyCreateSession;
        $this->companyUser = $companyUser ?:
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Company\Model\CompanyUser::class);
        $this->customerExtractor = $customerExtractor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->session = $customerSession;
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->helperdata = $helperdata;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailValidator = $emailValidator;
        $this->saveLoginAsCustomerAssistanceAllowed = $saveLoginAsCustomerAssistanceAllowed;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('customer/account/create');
        $data = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->validateRequest()) {
            return $resultRedirect;
        }

        try {
            if (!\Zend_Validate::is($data['company']['company_name'], 'NotEmpty')) {
                throw new InputException(
                    __("Please define all the required Business Details.")
                );
            }
            if (!\Zend_Validate::is($data['email'], 'NotEmpty')) {
                throw new InputException(
                    __("Please define all the required Basic Details.")
                );
            }
            $isEmailAddress = $this->emailValidator->isValid($data['email']);

            if (!$isEmailAddress) {
                throw new InputException(
                    __(
                        'Please enter valid email id : "%value"',
                        ['fieldName' => 'Email', 'value' => $data['email']]
                    )
                );
            }

            if ($this->checkIfLoggedCustomerIsACompanyMember()) {
                /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
                $resultForward = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
                $resultForward->setModule('company');
                $resultForward->setController('accessdenied');
                $resultForward->forward('index');
                return $resultForward;
            }

            $customer = $this->customerDataFactory->create();

            /* start create customer accounts */
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];
            $customer = $this->customerExtractor->extract('customer_account_create', $request);
            $customer->setAddresses($addresses);
            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            //$redirectUrl = $this->session->getBeforeAuthUrl();
            $this->checkPasswordConfirmation($password, $confirmation);

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
            $customer->setExtensionAttributes($extensionAttributes);

            $customer = $this->customerAccountManagement
                ->createAccount($customer, $password);
            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            /*set Customer session for when user register success*/
            $this->session->setCustomerDataAsLoggedIn($customer);
            /*End*/

            /* end create customer accounts */

            $this->companyCreateSession->setCustomerId($customer->getId());

            /*bv-hd customization for Allow remote shopping assistance */
            $customerId = $customer->getId();
            if ($customerId) {
                $assistanceAllowed = $this->saveLoginAsCustomerAssistanceAllowed->execute((int) $customerId);
            }
            /*bv-hd customization for Allow remote shopping assistance */

            $this->helperdata->sendEmail($data);

            $resultJson->setData([
                'status' => 'success',
                'message' => 'Your accout has been created successfully.',
                'country' => $data['country_id'],
                'id' => $customer->getId(),
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $resultJson->setData([
                'status' => 'error',
                'message' => $e->getMessage(),
                'country' => null,
                'id' => null,
            ]);
        } catch (\Exception $e) {
            $resultJson->setData([
                'status' => 'error',
                'message' => 'An error occurred on the server. Your changes have not been saved.',
                'country' => null,
                'id' => null,
            ]);
            $this->logger->critical($e);
        }

        return $resultJson;
    }

    /**
     * Validate request
     *
     * @return bool
     */
    private function validateRequest(): bool
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return false;
        }

        if (!$this->captchaValidator->validate($this->formId, $this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            return false;
        }

        return true;
    }

    /**
     * Method checks if logged customer is a company customer
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkIfLoggedCustomerIsACompanyMember(): bool
    {
        try {
            return (bool) $this->companyUser->getCurrentCompanyId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        $userDefinedAttr = $this->getRequest()->getParam('address') ?: [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->isUserDefined()) {
                $value = array_key_exists($attributeCode, $userDefinedAttr) ? $userDefinedAttr[$attributeCode] : null;
            } else {
                $value = $this->getRequest()->getParam($attributeCode);
            }

            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressData = $addressForm->compactData($addressData);
        unset($addressData['region_id'], $addressData['region']);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $addressDataObject;
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }
}
