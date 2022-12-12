<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Customer;

class Customer
{
    protected $customerSession;
    protected $checkoutSession;
    protected $accountManagement;
    protected $region;
    protected $customer;
    protected $maleGender = 1;
    protected $femaleGender = 2;
    protected $femaleCustomerGender = 'F';
    protected $maleCustomerGender = 'M';
    protected $orderCollectionFactory;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Directory\Model\Region $region,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->customer = $customerSession->getCustomer();
        $this->accountManagement = $accountManagement;
        $this->region = $region;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * get a customer session
     * @return object
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * get id
     * @return object
     */
    public function getId()
    {
        return $this->customer->getId();
    }

    /**
     * check if a customer already login
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * get a gender
     * @return boolean|string
     */
    public function getGender()
    {
        if ($this->isLoggedIn()) {
            if ($this->customer->getGender() == $this->maleGender) {
                return $this->maleCustomerGender;
            } elseif ($this->customer->getGender() == $this->femaleGender) {
                return $this->femaleCustomerGender;
            }
        }else {
            if ($this->checkoutSession->getQuote()->getCustomerGender() == $this->maleGender) {
                return $this->maleCustomerGender;
            } elseif ($this->checkoutSession->getQuote()->getCustomerGender() == $this->femaleGender) {
                return $this->femaleCustomerGender;
            }
        }

        return false;
    }

    /**
     * get a date of birth
     * @return date
     */
    public function getDob()
    {
        if ($this->isLoggedIn()) {
    	   return $this->customer->getDob();
        }else {
           return $this->checkoutSession->getQuote()->getCustomerDob();
        }
    }

    /**
     * get a default billing address
     * @return array
     */
    public function getDefaultBillingAddress()
    {
        $defaultBillingAdrress = array();
        $billingAddress = $this->accountManagement->getDefaultBillingAddress($this->customer->getId());
        if (!isset($billingAddress)) {
            return $defaultBillingAdrress;
        }

        $regionId = $billingAddress->getRegionId();
        if (isset($regionId)) {
            $this->region->load($regionId);
        }

        $defaultBillingAdrress = array();
        $defaultBillingAdrress['billing']['street'] = implode(' ', $billingAddress->getStreet());
        $defaultBillingAdrress['billing']['zip'] = $billingAddress->getPostcode();
        $defaultBillingAdrress['billing']['state'] = $this->region->getName();
        $defaultBillingAdrress['billing']['city'] = $billingAddress->getCity();
        $defaultBillingAdrress['billing']['countryCode'] = $billingAddress->getCountryId();

        return $defaultBillingAdrress;
    }

    /**
     * get a customer information
     * @return array
     */
    public function getCustomerInformation()
    {
        $customerInformation = array();
        $customerInformation['customer']['email'] = $this->customer->getEmail();
        $customerInformation['customer']['firstName'] = $this->customer->getFirstname();
        $customerInformation['customer']['lastName'] = $this->customer->getLastname();

        return $customerInformation;
    }

    /**
     * Get customer created date
     *
     * @return string|boolean
     */
    public function getCustomerCreatedDate()
    {
        $createdAt = $this->customer->getCreatedAt();
        if (isset($createdAt)) {
            $createdDate = strtotime($createdAt);
            return date('Y-m-d', $createdDate);
        }
        return date('Y-m-d');
    }

    /**
     * get a customer order count
     * @return int|boolean
     */
    public function getCustomerOrderCount()
    {
        if ($this->isLoggedIn()) {
            $orders = $this->orderCollectionFactory->create()
                ->addFieldToFilter('customer_id', $this->getId());

            return $orders->count();
        }

        return 0;
    }

    /**
     * get customer status
     *
     * @return string
     */
    public function getCustomerStatus()
    {
        if ($this->getCustomerOrderCount() > 0) {
            return 'BESTANDSKUNDE';
        }
        return 'NEUKUNDE';
    }

}
