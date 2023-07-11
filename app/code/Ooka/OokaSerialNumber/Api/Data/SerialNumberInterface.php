<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SerialNumberInterface extends ExtensibleDataInterface
{
    public const ORDER_ID = 'order_id';
    public const SKU = 'sku';
    public const SERIAL_CODE = 'serial_code';
    public const CUSTOMER_EMAIL = 'customer_email';

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param int $orderId
     * @return mixed
     */
    public function setOrderId(int $orderId);

    /**
     * Get sku
     *
     * @return mixed
     */
    public function getSku();

    /**
     * Set sku
     *
     * @param string $sku
     * @return mixed
     */
    public function setSku($sku);

    /**
     * Get serial code
     *
     * @return mixed
     */
    public function getSerialCode();

    /**
     * Set serial code
     *
     * @param string $serialCode
     * @return mixed
     */
    public function setSerialCode($serialCode);

    /**
     * Get customer email
     *
     * @return mixed
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @param string $customerEmail
     * @return mixed
     */
    public function setCustomerEmail($customerEmail);
}
