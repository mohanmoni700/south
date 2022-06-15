<?php
namespace SetuBridge\ChangeCustomerpwbyadmin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ChangeCustomerPw implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->authSession = $authSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {

            $customerParam=$this->request->getParam('customer');
            if (isset($customerParam['password_management'])) {
                $passwordManagement=$customerParam['password_management'];
                if (!empty($passwordManagement) && !empty($passwordManagement['new_password'])) {
                    $customer=$observer->getEvent()->getCustomer();
                    if ($this->getCurrentUser()->verifyIdentity($passwordManagement['current_user_password'])) {
                        $customer->setPassword($passwordManagement['new_password']);
                    } else {
                        $this->messageManager->addErrorMessage(__('Current User Identity Verification failed'));
                    }
                }
            }
        } catch (LocalizedException $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    private function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
}
