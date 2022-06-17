<?php

namespace HookahShisha\Customization\Observer;

use HookahShisha\Customization\Helper\Data;
use Magento\Framework\Event\ObserverInterface;

class GetCustomerData implements ObserverInterface
{
    /**
     * Constructor
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        Data $helper
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_pageFactory = $pageFactory;
        $this->helper = $helper;
    }
    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerdata = [];
        $customer = $observer->getEvent()->getCustomer();
        $email = $customer->getEmail();
        $firstname = $customer->getFirstName();
        $lastname = $customer->getLastName();
        $customerdata = ["email" => $email,
            "first_name" => $firstname,
            "last_name" => $lastname];
        $this->helper->customerData($customerdata);
    }
}
