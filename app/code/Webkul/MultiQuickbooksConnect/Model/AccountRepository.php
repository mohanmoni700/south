<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MultiQuickbooksConnect
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model;

class AccountRepository implements \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface
{
    /**
     * @var AccountFactory
     */
    private $quickbooksAccount;

    /**
     * @param AccountFactory $quickbooksAccount
     */
    public function __construct(
        AccountFactory $quickbooksAccount
    ) {
        $this->quickbooksAccount = $quickbooksAccount;
    }

    /**
     * @param  int $id
     * @return object
     */
    public function getById($id)
    {
        $account = $this->quickbooksAccount->create()->load($id);
        return $account;
    }

    /**
     * @param  int $accountName
     * @return object
     */
    public function getByAccountName($accountName)
    {
        $account = $this->quickbooksAccount->create()->getCollection()
                        ->addFieldToFilter('account_name', ['eq' => $accountName])
                        ->setPageSize(1)->getFirstItem();
        return $account;
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function getByStoreId($storeId)
    {
        $account = $this->quickbooksAccount->create()->getCollection()
                        ->addFieldToFilter('store_id', ['eq' => $storeId])
                        ->setPageSize(1)->getFirstItem();
        return ($account && $account->getId()) ? $account : false;
    }
}
