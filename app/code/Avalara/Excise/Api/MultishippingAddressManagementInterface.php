<?php

namespace Avalara\Excise\Api;

/**
 * Interface MultishippingAddressManagementInterface
 */
interface MultishippingAddressManagementInterface
{
    /**
     * @param Data\AddressInterface $address
     * @return bool
     */
    public function execute(Data\AddressInterface $address): bool;
}
