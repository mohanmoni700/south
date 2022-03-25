<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Helper\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Data\IPPEmailAddress;
use QuickBooksOnline\API\Data\IPPTelephoneNumber;
use QuickBooksOnline\API\Data\IPPPhysicalAddress;
use Magento\Directory\Model\CountryFactory;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Account as QuickBookBankAccount;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Account
     */
    private $quickBookBankAccount;

    /**
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param CountryFactory $countryFactory,
     * @param QuickBookBankAccount $quickBookBankAccount
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CountryFactory $countryFactory,
        QuickBookBankAccount $quickBookBankAccount
    ) {
        parent::__construct($context);
        $this->countryFactory = $countryFactory;
        $this->quickBookBankAccount = $quickBookBankAccount;
    }

    /**
     * getCustomerFields
     * @param array $customerData
     * @return IPPCustomer
     */
    public function getCustomerFields($customerData)
    {
        $customerObj = new IPPCustomer();
        $customerObj->DisplayName = $customerData['GivenName'].' ( '.$customerData['email'].' )';
        $customerObj->GivenName = $customerData['GivenName'];
        $customerObj->MiddleName = $customerData['MiddleName'];
        $customerObj->FamilyName = $customerData['FamilyName'];
        $customerObj->Title = isset($customerData['Title']) ? $customerData['Title'] : '';
        $customerObj->Suffix = isset($customerData['Suffix']) ? $customerData['Suffix'] : '';
        $customerObj->Organization = 'false';
        $customerObj->CompanyName = $customerData['CompanyName'];
        $customerObj->Active = 'true';

        $customerObj->PrimaryPhone = $this->getPrimaryPhone($customerData['bill_address']['telephone']);
        $customerObj->Fax = $this->getPrimaryPhone($customerData['bill_address']['fax']);
        $customerObj->PrimaryEmailAddr = $this->getEmailAddress($customerData['email']);
        $customerObj->ContactName = $customerData['GivenName'];
        $customerObj->AltContactName = $customerData['GivenName'];
        $customerObj->Notes = "Testing Notes";
        $customerObj->AcctNum = "Test020102";
        $customerObj->Job = 'false';
        $customerObj->BillAddr = $this->getPhysicalAddress($customerData['bill_address']);
        $customerObj->ShipAddr = $this->getPhysicalAddress($customerData['ship_address']);
        return $customerObj;
    }

    /**
     * createCustomer
     * @param DataService $dataService
     * @param array $customerData
     * @return IPPCustomer
     */
    public function createCustomer(DataService $dataService, $customerData)
    {
        return $dataService->Add($this->getCustomerFields($customerData));
    }

    /**
     * getCustomer
     * @param DataService $dataService
     * @param array $customerData
     * @return IPPCustomer
     */
    public function getCustomer(DataService $dataService, $customerData)
    {
        // @codingStandardsIgnoreStart
        $query = "SELECT * FROM Customer Where PrimaryEmailAddr ='".$customerData['email']."'";
        // @codingStandardsIgnoreEnd
        $customer = $dataService->Query($query);
        if (!$customer) {
            return $this->createCustomer($dataService, $customerData);
        } else {
            return $customer[0];
        }
    }

    /**
     * getEmailAddress
     * @param string $email
     * @return IPPEmailAddress
     */
    private function getEmailAddress($email)
    {
        $emailAddr = new IPPEmailAddress();
        $emailAddr->Address = $email;
        return $emailAddr;
    }

    /**
     * getEmailAddress
     * @param array $address
     * @return IPPPhysicalAddress
     */
    private function getPhysicalAddress($addressData)
    {
        $address = new IPPPhysicalAddress();
        $address->Line1 = $addressData['street'];
        $address->City = $addressData['city'];
        $address->Country = $this->countryFactory->create()->loadByCode($addressData['country_id'])->getName();
        $address->CountrySubDivisionCode = $addressData['region'];
        $address->PostalCode  = $addressData['postcode'];
        return $address;
    }

    /**
     * getPrimaryPhone
     * @param array $address
     * @return IPPPhysicalAddress
     */
    public function getPrimaryPhone($phoneNum)
    {
        $primaryNum = new IPPTelephoneNumber();
        $primaryNum->FreeFormNumber = substr($phoneNum, 0, 15);
        $primaryNum->Default = 'true';
        $primaryNum->Tag = "Business";
        return $primaryNum;
    }
}
