<?php

namespace HookahShisha\ChangePassword\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $_storemanager;

    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        Escaper $escaper,
        CustomerRepositoryInterface $customerRepository,
        Customer $customer,
        StoreManagerInterface $storemanager,
        \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $collection
    ) {
        $this->escaper = $escaper;
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->_storemanager = $storemanager;
        $this->collection = $collection;
        parent::__construct($context, $customerSession, $customerAccountManagement, $customerHelperData, $formKeyValidator, $accountRedirect);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            $websiteID = $this->_storemanager->getStore()->getWebsiteId();
            $email = (string) $login['username'];
            $resultRedirect->setPath('customer/account/login/');
            if ($email) {
                $customerData = $this->customer->setWebsiteId($websiteID)->loadByEmail($email);
                $migrate_customer_value = "";
                if ($customerData->getId()) {
                    $customerData = $this->customerRepository->getById($customerData->getId());
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();

                    $migrate_customer = $customerData->getCustomAttribute('migrate_customer');

                    if (!empty($migrate_customer)) {
                        $migrate_customer_value = $migrate_customer->getValue();
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Please enter your email.'));
                return $resultRedirect;
            }
            /* Here we are checking the Reset password */
            if (!empty($login['username']) && !empty($migrate_customer_value) && $migrate_customer_value == 1) {
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */

                if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                    $this->session->setForgottenEmail($email);
                    $this->messageManager->addErrorMessage(
                        __('The email address is incorrect. Verify the email address and try again.')
                    );
                    return $resultRedirect;
                }

                try {
                    $this->customerAccountManagement->initiatePasswordReset(
                        $email,
                        AccountManagement::EMAIL_RESET
                    );
                } catch (NoSuchEntityException $exception) {
                    // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
                } catch (SecurityViolationException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('We\'re unable to send the password reset email.')
                    );
                }
                $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
                return $resultRedirect;
            } elseif (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customerData = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customerData);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }

                    $customer_id = $customerData->getId();

                    $doc_collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customer_id]);
                    $document = $doc_collection->getData();
                    $dataSize = count($document);
                    $status = [];
                    foreach ($document as $value) {
                        $status[] = $value['status'];
                    }

                    if (in_array(0, $status) || empty($dataSize)) {
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('mydocument/customer/index');
                        return $resultRedirect;

                    } else {
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('');
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'confirmAccountErrorMessage',
                        ['url' => $this->customerUrl->getEmailConfirmationUrl($login['username'])]
                    );
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}
