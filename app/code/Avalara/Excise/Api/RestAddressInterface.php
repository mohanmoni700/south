<?php

namespace Avalara\Excise\Api;

use Avalara\Excise\Exception\AddressValidateException;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Magento\Framework\DataObject;

interface RestAddressInterface extends RestInterface
{
    /**
     * Perform REST request to validate address
     *
     * @param \Magento\Framework\DataObject $request
     * @param bool|null                     $isProduction
     * @param string|int|null               $scopeId
     * @param string|null                   $scopeType
     *
     * @return \Avalara\Excise\Framework\Interaction\Rest\Address\Result
     * @throws AddressValidateException
     * @throws AvalaraConnectionException
     */
    public function validate(
        $request,
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
