<?php
namespace HookahShisha\ChangePassword\Controller\Ajax;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class CheckMigratedCustomer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $resultJsonFactory;

    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $_storemanager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storemanager
     * @param Customer $customer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storemanager,
        Customer $customer
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->_storemanager = $storemanager;
        $this->customer = $customer;
        return parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $websiteID = $this->_storemanager->getStore()->getWebsiteId();
        $email = $this->getRequest()->getParam('email');

        $customer = $this->customer;
        if ($websiteID) {
            $customer->setWebsiteId($websiteID);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            $customer = $this->customerRepository->getById($customer->getId());
            $migrated_customer = $customer->getCustomAttribute('migrated_customer');
            if (!empty($migrated_customer)) {
                $migrated_customer_value = $migrated_customer->getValue();
                $response = [
                    'migrated_customer' => $migrated_customer_value,
                ];
            } else {
                $response = [
                    'migrated_customer' => null,
                ];

            }
        } else {
            $response = [
                'errors' => true,
                'message' => __('Invalid Customer'),
            ];
        }
        return $resultJson->setData($response);
    }
}
