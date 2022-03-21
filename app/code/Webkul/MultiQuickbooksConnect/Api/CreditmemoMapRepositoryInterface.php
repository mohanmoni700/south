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
interface CreditmemoMapRepositoryInterface
{
    /**
     * @param  int $id
     * @return object
     */
    public function getById($id);

    /**
     * get by magento creditmemo id
     *
     * @param string $mageCreditmemoId
     * @return object
     */
    public function getByMageCreditmemoId($mageCreditmemoId);

    /**
     * get collection by entity ids
     * @param  array $entityIds
     * @return object
     */
    public function getCollectionByIds(array $entityIds);

    /**
     * Get mapped creditmemo collection by quickbooks account id
     *
     * @param string $accountId
     * @return void
     */
    public function getCollectionByAccountId($accountId, $joinFlag);
}
