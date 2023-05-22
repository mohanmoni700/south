<?php

namespace Avalara\Excise\Api\Rest;

use Magento\Framework\DataObject;
use Avalara\Excise\Api\RestInterface;

interface CompanyInterface extends RestInterface
{
    /**
     * Perform REST request to get companies associated with the account
     *
     * @param DataObject|null $request
     * @param string|null $type
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    public function getCompanies(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
