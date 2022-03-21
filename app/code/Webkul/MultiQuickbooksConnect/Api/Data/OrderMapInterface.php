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

interface OrderMapInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const MAGE_ORDER_ID = 'mage_order_id';
    const MAGE_INVOICE_ID = 'mage_invoice_id';
    const QUICKBOOK_SALES_RECEIPT_ID = 'quickbook_sales_receipt_id';
    const QUICKBOOK_SALES_DOC_NUMBER = 'quickbook_sales_doc_number';
    const CREATED_AT = 'created';
    const ACCOUNT_ID = 'account_id';

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
     * Get MageOrderId.
     * @return string
     */
    public function getMageOrderId();

    /**
     * set MageOrderId.
     * @return $this
     */
    public function setMageOrderId($mageOrderId);

    /**
     * Get MageInvoiceId.
     *
     * @return varchar
     */
    public function getMageInvoiceId();

    /**
     * Set MageInvoiceId.
     */
    public function setMageInvoiceId($mageInvoiceId);

    /**
     * Get QuickbookSalesReceiptId.
     * @return string
     */
    public function getQuickbookSalesReceiptId();

    /**
     * set QuickbookSalesReceiptId.
     * @return $this
     */
    public function setQuickbookSalesReceiptId($quickbookSalesReceiptId);

    /**
     * Get QuickbookSalesDocNumber.
     * @return string
     */
    public function getQuickbookSalesDocNumber();

    /**
     * set QuickbookSalesDocNumber.
     * @return $this
     */
    public function setQuickbookSalesDocNumber($quickbookSalesDocNumber);

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
}
