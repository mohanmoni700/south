<?php

namespace Avalara\Excise\Logger;

use Monolog\Logger;
use Avalara\Excise\Api\Rest\LoggerApiInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\DataObject;

/**
 * Custom logger class
 */
class ExciseLogger extends Logger
{
    /**
     * @var LoggerApiInterface
     */
    protected $loggerApi;

    /**
     * ExciseLogger constructor.
     * @param LoggerApiInterface $loggerApi
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        LoggerApiInterface $loggerApi,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
        $this->loggerApi = $loggerApi;
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
        $obj->setLogType(LoggerApiInterface::LOG_TYPE['performance']);
        $obj->setLogLevel(LoggerApiInterface::LOG_LEVEL['info']);
        $obj->setFunctionName($functionName);
        $obj->setOperation($operationName);
        $obj->setSource($source);
        $time1 = $connectorTime['end'] - $connectorTime['start'];
        $time2 = $latencyTime['end'] - $latencyTime['start'];
        if ($time2 < 0) {
            $time2 = 0;
        }
        $time1 = $time1 - $time2;
        $obj->setConnectorTime(number_format($time1, 2, '.', ','));
        $obj->setConnectorLatency(number_format($time2, 2));
        try {
            return $this->loggerApi->makeTransactionRequest($obj, $scopeId, $scopeType);
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
     * @param DataObject $name
     * @param string|int|null $scopeId
     * @param string $scopeType
     * @param string $functionName
     * @param string $operationName
     * @param Exception $exceptionObject
     * @param string $source
     * @param string $message
     * @param string $docCode
     * @param string $docType
     * @param string $lineCount
     * @param string $eventBlock
     * @param string $logLevel
     *
     * @return bool
     */
    public function logDebugMessage(
        $obj,
        $scopeId,
        $scopeType,
        $functionName,
        $operationName,
        $exceptionObj,
        $source,
        $logMessageType = "",
        $message = "",
        $docCode = "",
        $docType = "",
        $lineCount = 1,
        $eventBlock = "",
        $logLevel = "exception"
    ) {
        $ret = true;
        $obj->setLogType(LoggerApiInterface::LOG_TYPE['debug']);
        $obj->setLogLevel(LoggerApiInterface::LOG_LEVEL[$logLevel]);
        $obj->setFunctionName($functionName);
        $obj->setOperation($operationName);
        $obj->setSource($source);
        $obj->setMessage($message);
        $obj->setMessage($logMessageType);
        $obj->setDocCode($docCode);
        $obj->setDocType($docType);
        $obj->setLineCount($lineCount);
        $obj->setEventBlock($eventBlock);
        if (is_object($exceptionObj) && method_exists($exceptionObj, 'getTraceAsString')) {
            $obj->setStackTrace($exceptionObj->getTraceAsString());
        }
        try {
            return $this->loggerApi->makeDebugLogRequest($obj, $scopeId, $scopeType);
        } catch (\Avalara\Excise\Exception\AvalaraConnectionException $exp) {
            $this->critical($exp->getMessage());
            $ret = false;
        } catch (\Exception $e) {
            $this->critical($e->getMessage());
            $ret = false;
        }
        return $ret;
    }

    /**
     * Log config value changes
     *
     * @param DataObject $obj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return void
     */
    public function configLog($obj, $scopeId, $scopeType)
    {
        $ret = true;
        try {
            $this->loggerApi->makeConfChangeRequest($obj, $scopeId, $scopeType);
        } catch (\Avalara\Excise\Exception\AvalaraConnectionException $exp) {
            $this->critical($exp->getMessage());
            $ret = false;
        } catch (\Exception $e) {
            $ret = false;
            $this->critical($e->getMessage());
        }
        return $ret;
    }

    /**
     * Log ping test changes
     *
     * @param DataObject $obj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return void
     */
    public function pingLog($obj, $scopeId, $scopeType)
    {
        $ret = true;
        try {
            $this->loggerApi->makeTestConnectionRequest($obj, $scopeId, $scopeType);
        } catch (\Avalara\Excise\Exception\AvalaraConnectionException $exp) {
            $ret = false;
        } catch (\Exception $e) {
            $ret = false;
        }
        return $ret;
    }
}
