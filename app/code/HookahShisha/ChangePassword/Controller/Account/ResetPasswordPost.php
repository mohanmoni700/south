<?php
namespace HookahShisha\ChangePassword\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;

/**
 * Customer reset password controller
 */
class ResetPasswordPost extends \Magento\Customer\Controller\Account\ResetPasswordPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param GetCustomerByToken $getByToken = null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        GetCustomerByToken $getByToken = null
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->getByToken = $getByToken
        ?: $objectManager->get(GetCustomerByToken::class);
        parent::__construct($context, $customerSession, $accountManagement, $customerRepository);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string) $this->getRequest()->getQuery('token');
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('password_confirmation');

        $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);
        if ($password !== $passwordConfirmation) {
            $this->messageManager->addErrorMessage(__("New Password and Confirm New Password values didn't match."));
            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addErrorMessage(__('Please enter a new password.'));
            return $resultRedirect;
        }

        try {

            $customer = $this->getByToken->execute($resetPasswordToken);

            $this->accountManagement->resetPassword(
                null,
                $resetPasswordToken,
                $password
            );

            if ($customer->getCustomAttribute("migrate_customer")) {
                $customer->setCustomAttribute("migrate_customer", 0);
                $this->customerRepository->save($customer);
            }
            // logout from current session if password changed.
            if ($this->session->isLoggedIn()) {
                $this->session->logout();
                $this->session->start();
            }
            $this->session->unsRpToken();
            $this->messageManager->addSuccessMessage(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');

        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }

        return $resultRedirect;
    }
}
