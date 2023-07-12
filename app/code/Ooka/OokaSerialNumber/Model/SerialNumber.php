<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Model;

use Ooka\OokaSerialNumber\Api\Data\SerialNumberInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber as ResourceModel;

class SerialNumber extends AbstractExtensibleModel implements SerialNumberInterface
{
    /**
     * Initialize resourcemodel
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

        /**
         * Get order id
         *
         * @return int|mixed|null
         */
    public function getOrderId()
    {
        return $this->_getData(self::ORDER_ID);
    }

    /**
     * Set order id
     *
     * @param int $orderId
     * @return mixed|void
     */
    public function setOrderId(int $orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get Sku
     *
     * @return mixed|null
     */
    public function getSku()
    {
        return $this->_getData(self::SKU);
    }

    /**
     * Set Sku
     *
     * @param  string $sku
     * @return SerialNumber|mixed
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get serial code
     *
     * @return mixed|void
     */
    public function getSerialCode()
    {
        return $this->_getData(self::SERIAL_CODE);
    }

    /**
     * Set Serial code
     *
     * @param string $serialCode
     * @return mixed|void
     */
    public function setSerialCode($serialCode)
    {
        return $this->setData(self::SERIAL_CODE, $serialCode);
    }

    /**
     * Get Customer Email
     *
     * @return mixed|void
     */
    public function getCustomerEmail()
    {
        return $this->_getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Set Customer Email
     *
     * @param string $customerEmail
     * @return mixed|void
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }
}
