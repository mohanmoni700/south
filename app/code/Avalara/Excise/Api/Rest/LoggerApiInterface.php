<?php

namespace Avalara\Excise\Api\Rest;

use Magento\Framework\DataObject;
use Avalara\Excise\Api\RestInterface;
use Magento\Store\Model\ScopeInterface;

interface LoggerApiInterface extends RestInterface
{
    /**
     * Log Types
     */
    const LOG_TYPE = ['performance' => 'Performance', 'debug' => 'Debug', 'config' => 'ConfigAudit'];

    /**
     * Log Levels
     */
    const LOG_LEVEL = ['error' => 'Error', 'exception' => 'Exception', 'info' => 'Informational'];

    /**
     * Send details to looger API
     *
     * @param array|null $request
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return mixed
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    public function sendToLog(
        $request = [],
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    );
}
