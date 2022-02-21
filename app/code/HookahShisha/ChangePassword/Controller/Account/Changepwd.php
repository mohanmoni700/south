<?php

declare (strict_types = 1);

namespace HookahShisha\ChangePassword\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use \Magento\Customer\Api\AccountManagementInterface;

/**
 * Change password account action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Changepwd extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param AccountManagementInterface $customerAccountManagement
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        AccountManagementInterface $customerAccountManagement,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->session = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('changepassword/index/index');

        try {
            $customerId = $this->session->getCustomerId();

            $currentPassword = $this->getRequest()->getParam('current_password');
            $newPass = $this->getRequest()->getParam('password');
            $confPass = $this->getRequest()->getParam('password_confirmation');

            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $this->customerAccountManagement->changePasswordById($customerId, $currentPassword, $newPass);
            $this->messageManager->addSuccessMessage(__('You have updated the password successfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Your changes have not been saved.')
            );
        }

        return $resultRedirect;
    }
}
