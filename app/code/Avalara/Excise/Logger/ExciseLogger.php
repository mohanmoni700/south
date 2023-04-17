<?php

namespace Avalara\Excise\Logger;

use Monolog\Logger;
use Magento\Framework\DataObject;
use Avalara\Excise\Helper\ApiLog;

/**
 * Custom logger class
 * @codeCoverageIgnore
 */
class ExciseLogger extends Logger
{
    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * ExciseLogger constructor.
     * @param ApiLog $apiLog
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        ApiLog $apiLog,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
        $this->apiLog = $apiLog;
    }

    /**
     * log performance of code
     *
     * @param DataObject $name
     * @param string|int|null $scopeId
     * @param string $scopeType
     * @param array $connectorTime
     * @param array $latencyTime
     * @param string $functionName
     * @param string $operationName
     * @param string $source
     */
    public function logPerformance(
        $obj,
        $scopeId,
        $scopeType,
        $connectorTime,
        $latencyTime,
        $functionName,
        $operationName,
        $source
    ) {
        $ret = true;
        $logContext = [
            "source" => $source,
            "operation" => $operationName,
            "function_name" => $functionName,
            "extra" => [
                "LineCount" => $obj->getLineCount(),
                "EventBlock" => $obj->getEventBlock(),
                "DocType" => $obj->getDocType(),
                "DocCode" => $obj->getDocCode(),
                "ConnectorTime" => $connectorTime,
                "ConnectorLatency" => $latencyTime
            ]            
        ];
        try {
            return $this->apiLog->makeTransactionRequestLog($logContext, $scopeId, $scopeType);
        } catch (\Avalara\Excise\Exception\AvalaraConnectionException $exp) {
            $ret = false;
            $this->critical($exp->getMessage());
        } catch (\Exception $e) {
            $ret = false;
            $this->critical($e->getMessage());
        }
        return $ret;
    }

    /**
     * Debugger log
     *
     * @param string $functionName
     * @param string $operationName
     * @param Exception $exceptionObject
     * @param string $source
     * @param string|int|null $scopeId
     * @param string $scopeType
     * @param string $docCode
     * @param string $docType
     * @param string $lineCount
     * @param string $eventBlock
     * @param string $logLevel
     *
     * @return bool
     */
    public function logDebugMessage(
        $functionName,
        $operationName,
        $exceptionObj,
        $source = "application_logs",
        $scopeId = null, 
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $docCode = "",
        $docType = "",
        $lineCount = 1,
        $eventBlock = "",
        $logLevel = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_EXCEPTION
    ) {
        $ret = true;
        $logContext = [
            "message" => $exceptionObj->getMessage(),
            "source" => $source,
            "operation" => $operationName,
            "function_name" => $functionName,
            "log_level" => $logLevel,
            "extra" => [
                "LineCount" => $lineCount,
                "EventBlock" => $eventBlock,
                "DocType" => $docType,
                "DocCode" => $docCode
            ]
        ];
        if (is_object($exceptionObj) && method_exists($exceptionObj, 'getTraceAsString')) {
            $logContext["extra"]["StackTrace"] = $exceptionObj->getTraceAsString();
        }
        try {
            return $this->apiLog->makeTransactionDebugLog($logContext, $scopeId, $scopeType);
        } catch (\Avalara\Excise\Exception\AvalaraConnectionException $exp) {
            $this->critical($exp->getMessage());
            $ret = false;
        } catch (\Exception $e) {
            $this->critical($e->getMessage());
            $ret = false;
        }
        return $ret;
    }
}
