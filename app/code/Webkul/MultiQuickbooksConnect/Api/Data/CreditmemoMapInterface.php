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

interface CreditmemoMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const MAGE_CREDITMEMO_ID = 'mage_creditmemo_id';
    const QUICKBOOK_CREDITMEMO_ID = 'quickbook_creditmemo_id';
    const QUICKBOOK_CREDITMEMO_DOC_NUMBER = 'quickbook_creditmemo_doc_number';
    const ACCOUNT_ID = 'account_id';
    const CREATED_AT = 'created';

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

    /**
     * get MageCreditmemoId
     * @return string
     */
    public function getMageCreditmemoId();

    /**
     * set MageCreditmemoId
     * @return $this
     */
    public function setMageCreditmemoId($mageCreditmemoId);

    /**
     * get QuickbookCreditmemoId
     * @return string
     */
    public function getQuickbookCreditmemoId();

    /**
     * set QuickbookCreditmemoId
     * @return $this
     */
    public function setQuickbookCreditmemoId($qbCreditmemoId);

    /**
     * get QuickbookCreditmemoDocNumber
     * @return string
     */
    public function getQuickbookCreditmemoDocNumber();

    /**
     * set QuickbookCreditmemoDocNumber
     * @return $this
     */
    public function setQuickbookCreditmemoDocNumber($qbCreditmemoDocNumber);

    /**
     * Get AccountId.
     * @return string
     */
    public function getAccountId();

    /**
     * set AccountId.
     * @return $this
     */
    public function setAccountId($accountId);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
