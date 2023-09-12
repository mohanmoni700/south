<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;

class ProductAlertStockGuestUser extends \Magento\Framework\Model\AbstractExtensibleModel implements ProductAlertStockGuestUserInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ProductAlertStockGuestUser::class);
    }

    /**
     * @inheritdoc
     */
    public function getAlertStockId(): int
    {
        return $this->getData(self::ALERT_STOCK_ID);
    }

    /**
     * Set Alert stock Id
     *
     * @param int $alertStockId
     * @return ProductAlertStockGuestUser
     */
    public function setAlertStockId(int $alertStockId): ProductAlertStockGuestUser
    {
        return $this->setData(self::ALERT_STOCK_ID, $alertStockId);
    }

    /**
     * Get Email ID
     *
     * @return int
     */
    public function getEmailId(): int
    {
        return $this->getData(self::EMAIL_ID);
    }

    /**
     * Set Email Id
     *
     * @param string $emailId
     * @return ProductAlertStockGuestUser
     */
    public function setEmailId(string $emailId): ProductAlertStockGuestUser
    {
        return $this->setData(self::EMAIL_ID, $emailId);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ProductAlertStockGuestUser
     */
    public function setName(string $name): ProductAlertStockGuestUser
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getProductId(): int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product Id
     *
     * @param int $productId
     * @return ProductAlertStockGuestUser
     */
    public function setProductId(int $productId): ProductAlertStockGuestUser
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId(): int
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return ProductAlertStockGuestUser
     */
    public function setWebsiteId(int $websiteId): ProductAlertStockGuestUser
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return ProductAlertStockGuestUser
     */
    public function setStoreId(int $storeId): ProductAlertStockGuestUser
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getAddDate(): string
    {
        return $this->getData(self::ADD_DATE);
    }

    /**
     * Set Add Data
     *
     * @param string $addDate
     * @return ProductAlertStockGuestUser
     */
    public function setAddDate(string $addDate): ProductAlertStockGuestUser
    {
        return $this->setData(self::ADD_DATE, $addDate);
    }

    /**
     * @inheritdoc
     */
    public function getSendDate()
    {
        return $this->getData(self::SEND_DATE);
    }

    /**
     * Set Send Date
     *
     * @param string $sendDate
     * @return ProductAlertStockGuestUser
     */
    public function setSendDate(string $sendDate): ProductAlertStockGuestUser
    {
        return $this->setData(self::SEND_DATE, $sendDate);
    }

    /**
     * @inheritdoc
     */
    public function getSendCount(): int
    {
        return $this->getData(self::SEND_COUNT);
    }

    /**
     * Set Send Count
     *
     * @param int $sendCount
     * @return ProductAlertStockGuestUser
     */
    public function setSendCount(int $sendCount): ProductAlertStockGuestUser
    {
        return $this->setData(self::SEND_COUNT, $sendCount);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return ProductAlertStockGuestUser
     */
    public function setStatus(int $status): ProductAlertStockGuestUser
    {
        return $this->setData(self::STATUS, $status);
    }
}
