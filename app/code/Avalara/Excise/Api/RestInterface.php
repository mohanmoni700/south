<?php

namespace Avalara\Excise\Api;

use Avalara\Excise\Exception\AvalaraConnectionException;

interface RestInterface
{
    /**
     * Get an Avalara REST API client object
     *
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return \Avalara\Excise\Framework\AvalaraClientWrapper
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );

    /**
     * Ping Avalara REST service to verify connection/authentication
     *
     * @param string          $accountNo
     * @param string          $license
     * @param string|null     $mode
     * @param string|null     $type
     * @param null|string|int $scopeId
     * @param string          $scopeType
     *
     * @return bool
     * @throws AvalaraConnectionException
     * @throws \InvalidArgumentException
     */
    public function ping(
        $accountNo = null,
        $license = null,
        $mode = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
