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
namespace Webkul\MultiQuickbooksConnect\Api;

/**
 * @api
 */
interface AccountRepositoryInterface
{
    /**
     * @param  int $id
     * @return object
     */
    public function getById($id);

    /**
     * get by quickbooks account name
     *
     * @param string $accountName
     * @return object
     */
    public function getByAccountName($accountName);

    /**
     * @param int $storeId
     * @return void
     */
    public function getByStoreId($storeId);
}
