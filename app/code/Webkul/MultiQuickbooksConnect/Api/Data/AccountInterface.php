<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MultiQuickbooksConnect
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Api\Data;

interface AccountInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const STORE_ID = 'store_id';
    const ACCOUNT_NAME = 'account_name';
    const SALES_RECEIPT_CREATE_ON = 'sales_receipt_create_on';
    const US_STORE = 'us_store';
    const ASSET_ACCOUNT = 'asset_account';
    const INCOME_ACCOUNT = 'income_account';
    const EXPENSE_ACCOUNT = 'expense_account';
    const STATUS = 'status';
    const IS_AUTHENTICATED = 'is_authenticated';
    const IS_CURRENT = 'is_current';
    const OAUTH2_ACCESS_TOKEN = 'oauth2_access_token';
    const OAUTH2_ACCESS_TOKEN_EXPIRE_ON = 'oauth2_access_token_expire_on';
    const OAUTH2_REFRESH_TOKEN = 'oauth2_refresh_token';
    const OAUTH2_REFRESH_TOKEN_EXPIRE_ON = 'oauth2_refresh_token_expire_on';
    const REALM_ID = 'realm_id';
    const CREATED_AT = 'created_at';

    /**
     * Get ID.
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     * @return $this
     */
    public function setId($id);
}
