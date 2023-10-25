<?php

namespace Corra\Veratad\Model;

use Corra\Veratad\Model\ResourceModel\OrderExtended as ResourceModelOrderExtended;

class OrderExtended extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModelOrderExtended::class);
    }

    /**
     * Retrieve AgeVerifiedResponse from Order Extend Table
     *
     * @return bool|null
     */
    public function getIsAgeVerified()
    {
        return $this->getData('is_age_verified');
    }

    /**
     * Set AgeVerifiedResponse to Order Extend Table
     *
     * @param bool $is_age_verified
     * @return OrderExtended
     */
    public function setIsAgeVerified($is_age_verified)
    {
        return $this->setData('is_age_verified', $is_age_verified);
    }

    /**
     * Retrieve SalesOrderId from Order Extend Table
     *
     * @return array|mixed|null
     */
    public function getSalesOrderId()
    {
        return $this->getData('sales_order_id');
    }

    /**
     * Set SalesOrderId to Order Extend Table
     *
     * @param int $sales_order_id
     * @return OrderExtended
     */
    public function setSalesOrderId($sales_order_id)
    {
        return $this->setData('sales_order_id', $sales_order_id);
    }

    /**
     * Veratad DOB from Order Extend Table
     *
     * @return string|mixed|null
     */
    public function getVeratadDob()
    {
        return $this->getData('veratad_dob');
    }

    /**
     * Set Veratad DOB to Order Extend Table.
     *
     * @param string $veratad_dob
     * @return OrderExtended
     */
    public function setVeratadDob($veratad_dob)
    {
        return $this->setData('veratad_dob', $veratad_dob);
    }

    /**
     * Veratad Billing address status from Order Extend Table
     *
     * @return string|mixed|null
     */
    public function getVeratadBillingAddressStatus()
    {
        return $this->getData('veratad_billing_address_status');
    }

    /**
     * Set Veratad Billing address status to Order Extend Table.
     *
     * @param string $billing
     * @return OrderExtended
     */
    public function setVeratadBillingAddressStatus($billing)
    {
        return $this->setData('veratad_billing_address_status', $billing);
    }

    /**
     * Veratad shipping address status from Order Extend Table
     *
     * @return string|mixed|null
     */
    public function getVeratadShippingAddressStatus()
    {
        return $this->getData('veratad_shipping_address_status');
    }

    /**
     * Set Veratad Billing address status to Order Extend Table.
     *
     * @param string $shipping
     * @return OrderExtended
     */
    public function setVeratadShippingAddressStatus($shipping)
    {
        return $this->setData('veratad_shipping_address_status', $shipping);
    }

    /**
     * Veratad details from Order Extend Table
     *
     * @return string|mixed|null
     */
    public function getVeratadDetail()
    {
        return $this->getData('veratad_detail');
    }

    /**
     * Set Veratad Details to Order Extend Table.
     *
     * @param string $veratad_detail
     * @return OrderExtended
     */
    public function setVeratadDetail($veratad_detail)
    {
        return $this->setData('veratad_detail', $veratad_detail);
    }
}
