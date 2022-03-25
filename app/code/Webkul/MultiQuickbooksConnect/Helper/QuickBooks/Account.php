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
use QuickBooksOnline\API\Data\IPPAccount;
use IPPAccountClassificationEnum;

class Account extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
    ) {
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function getBankAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "Ba" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $accountClassificationEnum = new IPPAccountClassificationEnum();
        $account->Classification = $accountClassificationEnum::IPPACCOUNTCLASSIFICATIONENUM_ASSET;
        $account->AccountType = "Bank";
        $account->CurrentBalance = 0;
        $account->CurrentBalanceWithSubAccounts = 0;
        $account->TxnLocationType = "FranceOverseas";
        $account->AcctNum = "B" . rand(0, 999);
        return $account;
    }

    public function createBankAccount(DataService $dataService)
    {
        return $dataService->Add(self::getBankAccountFields());
    }

    public function getCashBankAccount(DataService $dataService)
    {
        $allAccounts = $dataService->FindAll('Account', 0, 500);
        foreach ($allAccounts as $account) {
            if ($account->AccountType == "Bank") {
                return $account;
            }
        }
        return createBankAccount($dataService);
    }

    public function getOtherCurrentAssetAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "Other CurrentAsset" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $accountClassificationEnum = new IPPAccountClassificationEnum();
        $account->Classification = $accountClassificationEnum::IPPACCOUNTCLASSIFICATIONENUM_ASSET;
        $account->AccountType = "Other Current Asset";
        $accountSubTypeEnum = new IPPAccountSubTypeEnum();
        $account->AccountSubType = $accountSubTypeEnum::IPPACCOUNTSUBTYPEENUM_OTHERCURRENTASSETS;
        $account->CurrentBalance = 0;
        $account->CurrentBalanceWithSubAccounts = 0;
        return $account;
    }

    public function createOtherCurrentAssetAccount(DataService $dataService)
    {
        return $dataService->Add(self::getOtherCurrentAssetAccountFields());
    }

    /**
     * getAssetAccount
     * @param DataService $dataService
     * @return array;
     */
    public function getAssetAccount(DataService $dataService)
    {
        $condition = "where AccountType='Other Current Asset' and AccountSubType='Inventory'";
        $allAccounts = $dataService->FindAll("Account", $condition, 0, 2);
        foreach ($allAccounts as $account) {
            return $account;
        }
        return createOtherCurrentAssetAccount($dataService);
    }

    public function getCreditCardBankAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "CreditCard" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $accountClassificationEnum = new IPPAccountClassificationEnum();
        $account->Classification = $accountClassificationEnum::IPPACCOUNTCLASSIFICATIONENUM_LIABILITY;
        $account->AccountType = "Credit Card";
        $accountSubTypeEnum = new IPPAccountSubTypeEnum();
        $account->AccountSubType = $accountSubTypeEnum::IPPACCOUNTSUBTYPEENUM_CREDITCARD;
        $account->CurrentBalance = 0;
        $account->CurrentBalanceWithSubAccounts = 0;
        return $account;
    }

    public function createCreditCardBankAccount(DataService $dataService)
    {
        return $dataService->Add(self::getCreditCardBankAccountFields());
    }

    public function getCreditCardBankAccount(DataService $dataService)
    {
        $allAccounts = $dataService->FindAll('Account', 0, 500);
        foreach ($allAccounts as $account) {
            if ($account->AccountType == "Credit Card") {
                return $account;
            }
        }
        return self::createCreditCardBankAccount($dataService);
    }

    /**
     * getIncomeBankAccountFields
     * @param DataService $dataService
     * @return IPPAccount
     */
    public function getIncomeBankAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "Income" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $account->AccountType = "Income";
        $account->AccountSubType = 'SalesOfProductIncome';
        $account->CurrentBalance = 0;
        $account->CurrentBalanceWithSubAccounts = 0;
        return $account;
    }

    /**
     * createIncomeBankAccount
     * @param DataService $dataService
     * @return array
     */
    public function createIncomeBankAccount(DataService $dataService)
    {
        return $dataService->Add(self::getIncomeBankAccountFields());
    }

    /**
     * getIncomeBankAccount
     * @param DataService $dataService
     * @return array
     */
    public function getIncomeBankAccount(DataService $dataService)
    {
        $condition = "where AccountType='Income' and AccountSubType='SalesOfProductIncome'";
        $allAccounts = $dataService->FindAll('Account', $condition, 0, 2);
        if ($allAccounts) {
            foreach ($allAccounts as $account) {
                return $account;
            }
        }
        return self::createIncomeBankAccount($dataService);
    }

    public function getExpenseBankAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "Expense" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $accountClassificationEnum = new IPPAccountClassificationEnum();
        $account->Classification = $accountClassificationEnum::IPPACCOUNTCLASSIFICATIONENUM_EXPENSE;
        $account->AccountType = "Expense";
        $accountSubTypeEnum = new IPPAccountSubTypeEnum();
        $account->AccountSubType = $accountSubTypeEnum::IPPACCOUNTSUBTYPEENUM_ADVERTISINGPROMOTIONAL;
        $account->CurrentBalance = 0;
        $account->CurrentBalanceWithSubAccounts = 0;
        return $account;
    }

    public function createExpenseBankAccount(DataService $dataService)
    {
        return $dataService->Add(self::getExpenseBankAccountFields());
    }

    /**
     * getExpenseBankAccount
     * @param DataService $dataService
     * @return array;
     */

    public function getExpenseBankAccount(DataService $dataService)
    {
        $condition = "where AccountType='Cost of Goods Sold' and AccountSubType='SuppliesMaterialsCogs'";
        $allAccounts = $dataService->FindAll('Account', $condition, 0, 2);
        foreach ($allAccounts as $account) {
            return $account;
        }
        return self::createExpenseBankAccount($dataService);
    }

    public function getLiabilityBankAccountFields()
    {
        $account = new IPPAccount();
        $account->Name = "Equity" . rand();
        $account->SubAccount = 'false';
        $account->Active = 'true';
        $accountClassificationEnum = new IPPAccountClassificationEnum();
        $account->Classification = $accountClassificationEnum::IPPACCOUNTCLASSIFICATIONENUM_LIABILITY;
        $account->AccountType = "Accounts Payable";
        $accountSubTypeEnum = new IPPAccountSubTypeEnum();
        $account->AccountSubType = $accountSubTypeEnum::IPPACCOUNTSUBTYPEENUM_ACCOUNTSPAYABLE;
        $account->CurrentBalance = 3000;
        $account->CurrentBalanceWithSubAccounts = 3000;
        return $account;
    }

    public function createLiabilityBankAccount(DataService $dataService)
    {
        return $dataService->Add(self::getLiabilityBankAccountFields());
    }

    public function getLiabilityBankAccount(DataService $dataService)
    {
        $allAccounts = $dataService->FindAll('Account', 0, 500);

        foreach ($allAccounts as $account) {
            if ($account->AccountType == "Accounts Payable") {
                return $account;
            }
        }
        return self::createLiabilityBankAccount($dataService);
    }

    public function getCheckBankAccount(DataService $dataService)
    {
        $allAccounts = $dataService->FindAll('Account', 0, 500);
        foreach ($allAccounts as $account) {
            if ($account->AccountType == "Bank") {
                return $account;
            }
        }
        return self::createBankAccount($dataService);
    }
}
