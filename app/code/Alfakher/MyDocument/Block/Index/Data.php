<?php
namespace Alfakher\MyDocument\Block\Index;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;

class Data extends \Magento\Framework\View\Element\Template
{
    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $collection,
        CustomerFactory $customer,
        AddressFactory $address,

        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->address = $address;

        $this->collection = $collection;

        parent::__construct($context, $data);
    }

    public function sayHello()
    {
        return __('Hello World');
    }

    public function getCustomerId()
    {
        $customerid = $this->customerSession->getCustomer()->getId();
        return $customerid;
    }

    public function getCustomercollection($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        return $customer;
    }

    public function getCustomeraddress($customerid)
    {
        $customer = $this->customer->create()->load($customerid);
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->address->create()->load($billingAddressId);
        return $billingAddress;

    }

    public function getDocumentCollection($customerid)
    {
        $collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerid]);
        return $collection;
    }

}
