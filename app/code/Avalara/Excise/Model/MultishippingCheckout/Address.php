<?php

namespace Avalara\Excise\Model\MultishippingCheckout;

use Avalara\Excise\Api\Data\AddressInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class Address extends AbstractExtensibleObject implements AddressInterface
{

    /**
     * @return int
     */
    public function getAddressId(): int
    {
        return $this->_get(self::ADDRESS_ID);
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->_get(self::REGION);
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->_get(self::STREET);
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->_get(self::CITY);
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * @return int
     */
    public function getQuoteId(): int
    {
        return $this->_get(self::QUOTE_ID);
    }

    /**
     * @param int $id
     * @return Address
     */
    public function setAddressId(int $id): Address
    {
        return $this->setData(self::ADDRESS_ID, $id);
    }

    /**
     * @param string $region
     * @return Address
     */
    public function setRegion(string $region): Address
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet(string $street): Address
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity(string $city): Address
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @param string $postCode
     * @return Address
     */
    public function setPostcode(string $postCode): Address
    {
        return $this->setData(self::POSTCODE, $postCode);
    }

    /**
     * @param int $id
     * @return Address
     */
    public function setQuoteId(int $id): Address
    {
        return $this->setData(self::QUOTE_ID, $id);
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * @param int $id
     * @return Address
     */
    public function setCustomerId(int $id): Address
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    /**
     * @return string
     */
    public function getAddressType(): string
    {
        return $this->_get(self::ADDRESS_TYPE);
    }

    /**
     * @param string $addressType
     * @return Address|mixed
     */
    public function setAddressType(string $addressType): Address
    {
        return $this->setData(self::ADDRESS_TYPE, $addressType);
    }

    /**
     * @return int
     */
    public function getCustomerAddressId(): int
    {
        return $this->_get(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * @param int $id
     * @return Address|mixed
     */
    public function setCustomerAddressId(int $id): Address
    {
        return $this->setData(self::CUSTOMER_ADDRESS_ID, $id);
    }

    /**
     * @param string $county
     * @return Address
     */
    public function setCounty($county): Address
    {
        return $this->setData(self::COUNTY, $county);
    }

    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->_get(self::COUNTY);
    }
}
