<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Alfakher\MyDocument\Api\Data;

interface MyDocumentInterface
{
    // @codingStandardsIgnoreStart
    const CUSTOMER_ID = 'customer_id';
    const FILENAME = 'filename';
    const MESSAGE = 'message';
    const STATUS = 'status';
    const DOCUMENT_NAME = 'document_name';
    const MYDOCUMENT_ID = 'mydocument_id';
    const CREATED_AT = 'created_at';
    const EXPIRY_DATE = 'expiry_date';
    const IS_CUSTOMERFROM_USA = 'is_customerfrom_usa';
    const EXPIRED_FLAG = 'notify_expire_doc_mail';
    // @codingStandardsIgnoreStart
    /**
     * Get mydocument_id

     * @return string|null
     */
    public function getMydocumentId();

    /**
     * Set mydocument_id

     * @param string $mydocumentId
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setMydocumentId($mydocumentId);

    /**
     * Get customer_id

     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id

     * @param string $customerId
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get is_customerfrom_usa

     * @return string|null
     */
    public function getIsCustomerfromUsa();

    /**
     * Set is_customerfrom_usa

     * @param string $isCustomerfromUsa
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setIsCustomerfromUsa($isCustomerfromUsa);

    /**
     * Get filename

     * @return string|null
     */
    public function getFilename();

    /**
     * Set filename

     * @param string $filename
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setFilename($filename);

    /**
     * Get status

     * @return string|null
     */
    public function getStatus();

    /**
     * Set status

     * @param string $status
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setStatus($status);

    /**
     * Get message

     * @return string|null
     */
    public function getMessage();

    /**
     * Set message

     * @param string $message
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setMessage($message);

    /**
     * Get document_name

     * @return string|null
     */
    public function getDocumentName();

    /**
     * Set document_name

     * @param string $documentName
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setDocumentName($documentName);

    /**
     * Get notify_expire_doc_mail

     * @return bool|null
     */
    public function getNotifyExpireDocMail();

    /**
     * Set notify_expire_doc_mail

     * @param string $expiredflag
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setNotifyExpireDocMail($expiredflag);

    /**
     * Get created_at

     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at

     * @param string $createdAt
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get expiry_date

     * @return string|null
     */
    public function getExpiryDate();

    /**
     * Set expiry_date

     * @param string $expiryDate
     * @return \Alfakher\MyDocument\MyDocument\Api\Data\MyDocumentInterface
     */
    public function setExpiryDate($expiryDate);
}
