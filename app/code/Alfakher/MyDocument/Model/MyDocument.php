<?php
declare (strict_types = 1);

namespace Alfakher\MyDocument\Model;

use Alfakher\MyDocument\Api\Data\MyDocumentInterface;
use Magento\Framework\Model\AbstractModel;

class MyDocument extends AbstractModel implements MyDocumentInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Alfakher\MyDocument\Model\ResourceModel\MyDocument::class);
    }

    /**
     * @inheritDoc
     */
    public function getMydocumentId()
    {
        return $this->getData(self::MYDOCUMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMydocumentId($mydocumentId)
    {
        return $this->setData(self::MYDOCUMENT_ID, $mydocumentId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getIsCustomerfromUsa()
    {
        return $this->getData(self::IS_CUSTOMERFROM_USA);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerfromUsa($isCustomerfromUsa)
    {
        return $this->setData(self::IS_CUSTOMERFROM_USA, $isCustomerfromUsa);
    }

    /**
     * @inheritDoc
     */
    public function getFilename()
    {
        return $this->getData(self::FILENAME);
    }

    /**
     * @inheritDoc
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function getDocumentName()
    {
        return $this->getData(self::DOCUMENT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setDocumentName($documentName)
    {
        return $this->setData(self::DOCUMENT_NAME, $documentName);
    }

    /**
     * @inheritDoc
     */
    public function getNotifyExpireDocMail()
    {
        return $this->getData(self::EXPIRED_FLAG);
    }

    /**
     * @inheritDoc
     */
    public function setNotifyExpireDocMail($expiredflag)
    {
        return $this->setData(self::EXPIRED_FLAG, $expiredflag);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDate()
    {
        return $this->getData(self::EXPIRY_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDate($expiryDate)
    {
        return $this->setData(self::EXPIRY_DATE, $expiryDate);
    }
}
