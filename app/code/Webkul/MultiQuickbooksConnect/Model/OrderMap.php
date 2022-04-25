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
namespace Webkul\MultiQuickbooksConnect\Model;

use Webkul\MultiQuickbooksConnect\Api\Data\OrderMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class OrderMap extends \Magento\Framework\Model\AbstractModel implements OrderMapInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'multi_quickbook_map_sales_receipt';

    /**
     * @var string
     */
    protected $_cacheTag = 'multi_quickbook_map_sales_receipt';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'multi_quickbook_map_sales_receipt';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MultiQuickbooksConnect\Model\ResourceModel\OrderMap::class);
    }

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get MageOrderId.
     *
     * @return varchar
     */
    public function getMageOrderId()
    {
        return $this->getData(self::MAGE_ORDER_ID);
    }

    /**
     * Set MageOrderId.
     */
    public function setMageOrderId($mageOrderId)
    {
        return $this->setData(self::MAGE_ORDER_ID, $mageOrderId);
    }

    /**
     * Get MageInvoiceId.
     *
     * @return varchar
     */
    public function getMageInvoiceId()
    {
        return $this->getData(self::MAGE_INVOICE_ID);
    }

    /**
     * Set MageInvoiceId.
     */
    public function setMageInvoiceId($mageInvoiceId)
    {
        return $this->setData(self::MAGE_INVOICE_ID, $mageInvoiceId);
    }

    /**
     * Get QuickbookSalesReceiptId.
     *
     * @return varchar
     */
    public function getQuickbookSalesReceiptId()
    {
        return $this->getData(self::QUICKBOOK_SALES_RECEIPT_ID);
    }

    /**
     * Set QuickbookSalesReceiptId.
     */
    public function setQuickbookSalesReceiptId($quickbookSalesReceiptId)
    {
        return $this->setData(self::QUICKBOOK_SALES_RECEIPT_ID, $quickbookSalesReceiptId);
    }

    /**
     * Get QuickbookSalesReceiptId.
     *
     * @return varchar
     */
    public function getQuickbookSalesDocNumber()
    {
        return $this->getData(self::QUICKBOOK_SALES_DOC_NUMBER);
    }

    /**
     * Set QuickbookSalesReceiptId.
     */
    public function setQuickbookSalesDocNumber($quickbookSalesDocNumber)
    {
        return $this->setData(self::QUICKBOOK_SALES_DOC_NUMBER, $quickbookSalesDocNumber);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get AccountId.
     *
     * @return varchar
     */
    public function getAccountId()
    {
        return $this->getData(self::ACCOUNT_ID);
    }

    /**
     * Set AccountId.
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::ACCOUNT_ID, $accountId);
    }
}
