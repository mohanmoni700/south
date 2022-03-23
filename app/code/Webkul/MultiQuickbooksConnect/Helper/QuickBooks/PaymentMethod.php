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

use QuickBooksOnline\API\Data\IPPPaymentMethod;
use QuickBooksOnline\API\DataService\DataService;

class PaymentMethod extends \Magento\Framework\App\Helper\AbstractHelper
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
     * getCustomerFields
     * @param array $customerData
     * @return IPPCustomer
     */
    public function getPaymentMethodFields($paymentMethodData)
    {
        $paymentMethodObj = new IPPPaymentMethod();
        $paymentMethodObj->Name = $paymentMethodData;
        $paymentMethodObj->Active = 'true';
        return $paymentMethodObj;
    }

    /**
     * createPaymentMethod
     * @param DataService $dataService
     * @param array $paymentMethodData
     * @return IPPCustomer
     */
    public function createPaymentMethod(DataService $dataService, $paymentMethodData)
    {
        return $dataService->Add($this->getPaymentMethodFields($paymentMethodData));
    }

    /**
     * getPaymentMethod
     * @param DataService $dataService
     * @param array $paymentMethod
     * @return IPPPaymentMethod
     */
    public function getPaymentMethod(DataService $dataService, $paymentMethodData)
    {
        // @codingStandardsIgnoreStart
        $query = "SELECT * FROM PaymentMethod Where Name ='".$paymentMethodData."'";
        // @codingStandardsIgnoreEnd
        $paymentMethod = $dataService->Query($query);
        if (!$paymentMethod) {
            return $this->createPaymentMethod($dataService, $paymentMethodData);
        } else {
            return $paymentMethod[0];
        }
    }
}
