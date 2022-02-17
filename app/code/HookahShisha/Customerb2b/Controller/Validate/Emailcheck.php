<?php
namespace HookahShisha\Customerb2b\Controller\Validate;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

class Emailcheck extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Manually constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param AccountManagementInterface $customerAccountManagement
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Customer $customerModel,
        AccountManagementInterface $customerAccountManagement,
        StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Action for emailCheck
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $email = $this->getRequest()->getParam('email');

        $websiteId = (int) $this->storeManager->getWebsite()->getId();
        $isEmailNotExists = $this->customerAccountManagement->isEmailAvailable($email, $websiteId);

        if ($isEmailNotExists) {
            $resultJson->setData('true');
        } else {
            $resultJson->setData('Email Already Exist, try another one.');
        }
        return $resultJson;
    }
}
