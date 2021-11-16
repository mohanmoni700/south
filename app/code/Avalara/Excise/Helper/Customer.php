<?php
namespace Avalara\Excise\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;

class Customer extends AbstractHelper
{
    /**
     * If user is guest, ID to use for "name_id" option
     */
    const CUSTOMER_GUEST_ID = 'Guest';
    
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CustomerRepositoryInterface                   $customerRepository
     * @param Config                                        $config
     * @param Context                                       $context
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        Context $context
    ) {
        parent::__construct($context);

        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * This method will attempt to retrieve the provided customer code value as a system-defined customer attribute; if
     * that fails, then it will attempt to retrieve the value as a custom attribute
     *
     * @param CustomerInterface $customer
     * @param string            $customerCode
     *
     * @return string
     */
    public function getCustomerAttributeValue($customer, $customerCode)
    {
        // Convert provided customer code to getter name
        $getCustomerCode = 'get' . str_replace('_', '', ucwords($customerCode, '_'));
        if (method_exists($customer, $getCustomerCode)) {
            // A method exists with this getter name, call it
            return $customer->{$getCustomerCode}();
        }
        // This was not a system-defined customer attribute, retrieve it as a custom attribute
        $attribute = $customer->getCustomAttribute($customerCode);
        if ($attribute===null) {
            // Retrieving the custom attribute failed, or no value was set, return null
            return '';
        }

        // Return value of custom attribute
        return (string)$attribute->getValue();
    }

    /**
     * @param CustomerInterface|null $customer
     *
     * @return string
     */
    public function generateCustomerCodeFromNameId($customer = null)
    {
        $name = Config::CUSTOMER_MISSING_NAME;
        $id = Config::CUSTOMER_GUEST_ID;

        if ($customer !== null && $customer->getId() !== null) {
            $name = "{$customer->getFirstname()} {$customer->getLastname()}";
            $id = $customer->getId();
        }

        return "{$name} ({$id})";
    }

    /**
     * @param CustomerInterface $customer
     * @param string            $attribute
     *
     * @return string|null
     */
    public function generateCustomerCodeFromAttribute($customer, $attribute)
    {
        // Retrieve attribute value using provided attribute code
        $attributeValue = $this->getCustomerAttributeValue($customer, $attribute);

        if ($attributeValue === null && $attribute === Config::CUSTOMER_FORMAT_OPTION_EMAIL) {
            return Config::CUSTOMER_MISSING_EMAIL;
        }

        if ($attributeValue !== null && $attributeValue !== '') {
            // Customer has a value defined for provided attribute code and the provided value is a string
            return $attributeValue;
        }

        return null;
    }

    /**
     * Retrieve the AvaTax customer code from the customer model
     *
     * @param CustomerInterface|null $customer
     * @param int|null               $uniqueGuestIdentifier such as the quote or order ID
     * @param int|null               $storeId
     *
     * @return string
     */
    public function getCustomerCode($customer, $uniqueGuestIdentifier = null, $storeId = null)
    {

        // As a fallback, use some unique identifier for the guest
        $customerCode = strtolower(CUSTOMER_GUEST_ID) . "-{$uniqueGuestIdentifier}";

        // If there is a customer, use their ID as a fallback
        if ($customer !== null && $customer->getId()) {
            $customerCode = $customer->getId();
            return $customerCode;
        }
        
        return $customerCode;
    }

    /**
     * Retrieve the AvaTax customer code from a customer id
     *
     * @param int      $customerId
     * @param int|null $uniqueGuestIdentifier such as the quote or order ID
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCustomerCodeByCustomerId($customerId, $uniqueGuestIdentifier = null, $storeId = null)
    {
        $customer = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            // Don't need to handle this, we expect the possibility of null
        } catch (LocalizedException $e) {
            // Don't need to handle this, we expect the possibility of null
        }

        return $this->getCustomerCode($customer, $uniqueGuestIdentifier, $storeId);
    }
}
