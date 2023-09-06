<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Api\Data;

use Alfakher\StockAlert\Model\Data\ProductAlertStockGuestUser;

interface ProductAlertStockGuestUserInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ALERT_STOCK_ID = 'alert_stock_id';
    public const EMAIL_ID = 'email_id';
    public const NAME = 'name';
    public const PRODUCT_ID = 'product_id';
    public const WEBSITE_ID = 'website_id';
    public const STORE_ID = 'store_id';
    public const ADD_DATE = 'add_date';
    public const SEND_DATE = 'send_date';
    public const SEND_COUNT = 'send_count';
    public const STATUS = 'status';

    /**
     * Get alert stock ID.
     *
     * @return int
     */
    public function getAlertStockId(): int;

    /**
     * Set alert stock ID.
     *
     * @param int $alertStockId
     * @return ProductAlertStockGuestUser
     */
    public function setAlertStockId(int $alertStockId): ProductAlertStockGuestUser;

    /**
     * Get email ID.
     *
     * @return int
     */
    public function getEmailId(): int;

    /**
     * Set email ID
     *
     * @param string $emailId
     * @return ProductAlertStockGuestUser
     */
    public function setEmailId(string $emailId);
    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set Name
     *
     * @param string $name
     * @return ProductAlertStockGuestUser
     */
    public function setName(string $name);

    /**
     * Get product ID.
     *
     * @return int
     */
    public function getProductId(): int;

    /**
     * Set Product ID
     *
     * @param int $productId
     * @return ProductAlertStockGuestUser
     */
    public function setProductId(int $productId);

    /**
     * Get website ID.
     *
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * Set Website ID
     *
     * @param int $websiteId
     * @return ProductAlertStockGuestUser
     */
    public function setWebsiteId(int $websiteId);

    /**
     * Get store ID.
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return ProductAlertStockGuestUser
     */
    public function setStoreId(int $storeId);

    /**
     * Get add date.
     *
     * @return string
     */
    public function getAddDate(): string;

    /**
     * Set Add Date
     *
     * @param string $addDate
     * @return ProductAlertStockGuestUser
     */
    public function setAddDate(string $addDate);

    /**
     * Get send date.
     *
     * @return string|null
     */
    public function getSendDate();

    /**
     * Set Send Date
     *
     * @param string $sendDate
     * @return ProductAlertStockGuestUser
     */
    public function setSendDate(string $sendDate);

    /**
     * Get send count.
     *
     * @return int
     */
    public function getSendCount(): int;

    /**
     * Set Send Count
     *
     * @param int $sendCount
     * @return ProductAlertStockGuestUser
     */
    public function setSendCount(int $sendCount);

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set Status
     *
     * @param int $status
     * @return ProductAlertStockGuestUser
     */
    public function setStatus(int $status);
}
