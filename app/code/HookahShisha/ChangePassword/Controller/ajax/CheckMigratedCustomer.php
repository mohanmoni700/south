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
     * @var Customer
     */
    private $customer;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $_storemanager;

    /**
     *
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

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $websiteID = $this->_storemanager->getStore()->getWebsiteId();
        $email = $this->getRequest()->getParam('email');

        $customerData = $this->customer->setWebsiteId($websiteID)->loadByEmail($email);
        if ($customerData->getId()) {
            $customerData = $this->customerRepository->getById($customerData->getId());
            $migrateCustomer = $customerData->getCustomAttribute('migrate_customer');
            if (!empty($migrateCustomer)) {
                $migrateCustomerValue = $migrateCustomer->getValue();
                $response = [
                    'migrate_customer' => $migrateCustomerValue,
                ];
            } else {
                $response = [
                    'migrate_customer' => null,
                ];

            }
        } else {
            $response = [
                'errors' => true,
                'message' => __('Please enter a valid email address.'),
            ];
        }
        return $resultJson->setData($response);
    }
}
