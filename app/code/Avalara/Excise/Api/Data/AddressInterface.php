<?php

namespace Avalara\Excise\Api\Data;

/**
 * Interface AddressInterface
 */
interface AddressInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ADDRESS_ID = 'address_id';
    const CUSTOMER_ID = 'customer_id';
    const REGION = 'region';
    const STREET = 'street';
    const CITY = 'city';
    const POSTCODE = 'postcode';
    const QUOTE_ID = 'quote_id';
    const ADDRESS_TYPE = 'address_type';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const COUNTY = 'county';
    /**#@-*/

    /**
     * @return int
     */
    public function getAddressId(): int;

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @return string
     */
    public function getRegion(): string;

    /**
     * @return string
     */
    public function getStreet(): string;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @return string
     */
    public function getPostcode(): string;

    /**
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @return string
     */
    public function getAddressType(): string;

    /**
     * @return int
     */
    public function getCustomerAddressId(): int;

    /**
     * @param int $id
     * @return mixed
     */
    public function setAddressId(int $id);

    /**
     * @param int $id
     * @return mixed
     */
    public function setCustomerId(int $id);

    /**
     * @param string $region
     * @return mixed
     */
    public function setRegion(string $region);

    /**
     * @param string $street
     * @return mixed
     */
    public function setStreet(string $street);

    /**
     * @param string $city
     * @return mixed
     */
    public function setCity(string $city);

    /**
     * @param string $postCode
     * @return mixed
     */
    public function setPostcode(string $postCode);

    /**
     * @param int $id
     * @return mixed
     */
    public function setQuoteId(int $id);

    /**
     * @param string $addressType
     * @return mixed
     */
    public function setAddressType(string $addressType);

    /**
     * @param int $id
     * @return mixed
     */
    public function setCustomerAddressId(int $id);

    /**
     * @return string
     */
    public function getCounty();

    /**
     * @param string $county
     * @return mixed
     */
    public function setCounty($county);
}
