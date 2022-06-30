<?php

namespace Alfakher\Shopify\Model\Api;

use Alfakher\Shopify\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerCreate implements CustomerRepositoryInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Initialize service
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Customer $customerResource,
        CustomerFactory $customerFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
    }

   /**
    * Create customer account.
    *
    * @param string $first_name
    * @param string $last_name
    * @param string $email
    * @return int $id
    */
    public function createCustomerAccount($first_name, $last_name, $email)
    {
        $customer = $this->customerFactory->create();
        $customer->setFirstname($first_name)
        ->setLastname($last_name)
        ->setEmail($email);
        $this->customerResource->save($customer);
        return $customer->getId();
    }
}
