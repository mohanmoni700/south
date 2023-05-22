<?php

namespace Avalara\Excise\Api;

/**
 * Interface ValidAddressManagementInterface
 */
interface ValidAddressManagementInterface
{
    /**
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param string $storeId
     * @return \Magento\Customer\Api\Data\AddressInterface|string
     */
    public function saveValidAddress(\Magento\Customer\Api\Data\AddressInterface $address, $storeId = null);
}
