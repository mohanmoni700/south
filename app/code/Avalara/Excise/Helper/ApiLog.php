<?php
/**
 * Avalara_Excise
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Avalara\Excise\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Avalara\Excise\BaseProvider\Logger\GenericLogger;
use Avalara\Excise\Framework\Constants;

/**
 * @codeCoverageIgnore
 */
class ApiLog extends AbstractHelper
{
    const CONNECTOR_ID = 'a0n5a00000ZmXLNAA3'; //Do not change this value as this is whitlisted for logging

    const CONNECTOR_STRING = Constants::CONNECTOR_ID;

    const CONNECTOR_NAME = Constants::SOURCE_SYSTEM; 

    const HTML_ESCAPE_PATTERN = '/<(.*) ?.*>(.*)<\/(.*)>/';                         

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GenericLogger
     */
    protected $genericLogger;

    /**
     * @param Config  $config
     * @param Context $context
     * @param GenericLogger $genericLogger
     */
    public function __construct(Config $config, Context $context, GenericLogger $genericLogger)
    {
        parent::__construct($context);

        $this->config = $config;
        $this->genericLogger = $genericLogger;
    }

    /**
     * API Logging
     *
     * @param string $message
     * @param array $context
     * @param $scopeId|Null
     * @param $scopeType|Null
     * @return void
     */
    public function apiLog(string $message, array $context = [], $scopeId = null, $scopeType = null)
    {
        if (strlen($message) > 0) {
            $isProduction = $this->config->isProductionMode($scopeId, $scopeType);
            $accountNumber = $this->config->getAvaTaxAccountNumber($scopeId, $scopeType);
            $accountSecret = $this->config->getAvaTaxLicenseKey($scopeId, $scopeType);
            $connectorId = self::CONNECTOR_ID;
            $clientString = self::CONNECTOR_STRING;
            $mode = $isProduction ? \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_MODE_PRODUCTION : \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_MODE_SANDBOX;
            $connectorName = self::CONNECTOR_NAME;
            $source = isset($context['config']['source']) ? $context['config']['source'] : 'MagentoPage';
            $operation = isset($context['config']['operation']) ? $context['config']['operation'] : 'MagentoOperation';
            $logType = isset($context['config']['log_type']) ? $context['config']['log_type'] : \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_DEBUG;
            $logLevel = isset($context['config']['log_level']) ? $context['config']['log_level'] : \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
            $functionName = isset($context['config']['function_name']) ? $context['config']['function_name'] : __METHOD__;
            
            $params = [
                'config' => [
                    'account_number' => $accountNumber,
                    'account_secret' => $accountSecret,
                    'connector_id' => $connectorId,
                    'client_string' => $clientString,
                    'mode' => $mode,
                    'connector_name' => $connectorName,
                    'connector_version' => $clientString,
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName,
                    'extra_params' => isset($context['config']['extra_params']) ? $context['config']['extra_params'] : []
                ]
            ];
            $this->genericLogger->apiLog($message, [$params]);
        }
    }

    /**
     * Log Connection with AvaTax
     *
     * @param string $message
     * @param $scopeId
     * @param $scopeType
     * @return void
     */
    public function testConnectionLog(string $message, $scopeId, $scopeType)
    {
        try {
            $source = 'ConfigurationPage';
            $operation = 'Test Connection';
            $logType = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_CONFIG;
            $logLevel = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
            $functionName = __METHOD__;
            $context = [
                'config' => [
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName
                ]
            ];
            $this->apiLog($message, $context, $scopeId, $scopeType);
        } catch(\Exception $e) {
            //do nothing as this is internal logging
        }
    }

    /**
     * configSaveLog API Logging
     *
     * @param $scopeId
     * @param $scopeType
     * @return void
     */
    public function configSaveLog($scopeId, $scopeType)
    {
        try {
            $message = "";
            $source = 'ConfigurationPage';
            $operation = 'ConfigChanges';
            $logType = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_CONFIG;
            $logLevel = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
            $functionName = __METHOD__;
            $context = [
                'config' => [
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName
                ]
            ];
            $data = $this->getConfigData($scopeId, $scopeType);
            $message = json_encode($data);
            $this->apiLog($message, $context, $scopeId, $scopeType);
        } catch(\Exception $e) {
            //do nothing as this is internal logging
        }
    }

    /**
     * TransactionRequest API Logging
     *
     * @param array $logContext
     * @param $scopeId
     * @param $scopeType
     * @return void
     */
    public function makeTransactionRequestLog(array $logContext, $scopeId, $scopeType)
    {
        try {
            $message = "Transaction Request Log";
            if (isset($logContext['extra']['amount'])) {
                $message .= " Total: ".$logContext['extra']['amount'];
            }
            if (isset($logContext['extra']['tax'])) {
                $message .= " Tax: ".$logContext['extra']['tax'];
            }
            $source = isset($logContext['source']) ? $logContext['source'] : 'TransactionPage';
            $operation = isset($logContext['operation']) ? $logContext['operation'] : 'TransactionOperation';
            $logType = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_PERFORMANCE;
            $logLevel = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_INFO;
            $functionName = isset($logContext['function_name']) ? $logContext['function_name'] : __METHOD__;
            if (isset($logContext['extra']['ConnectorTime']) && isset($logContext['extra']['ConnectorLatency']) ) {
                list($connectorTime, $latencyTime) = $this->getLatencyTimeAndConnectorTime($logContext['extra']);
                unset($logContext['extra']['ConnectorTime']);
                unset($logContext['extra']['ConnectorLatency']);
                if (!is_null($connectorTime))
                    $logContext['extra']['ConnectorTime'] = intval($connectorTime * 1000);
                if (!is_null($latencyTime))
                    $logContext['extra']['ConnectorLatency'] = intval($latencyTime * 1000);
            }
            $context = [
                'config' => [
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName,
                    'extra_params' => isset($logContext['extra']) ? $logContext['extra'] : []
                ]
            ];
            $this->apiLog($message, $context, $scopeId, $scopeType);
        } catch(\Exception $e) {
            //do nothing as this is internal logging
        }
    }

    /**
     * getConfigData function
     *
     * @param $scopeId
     * @param $scopeType
     * @return array
     */
    private function getConfigData($scopeId, $scopeType)
    {
        $notRequired = [
            '__construct', 'getExciseLicenseKey', 'getAvaTaxLicenseKey',
            'getApplicationName', 'getApplicationDomain', 'getTimeZoneObject',
            'getPriceIncludesTax', 'getOriginAddress', 'isAddressTaxable', '_getRequest',
            '_getModuleName', 'isModuleOutputEnabled', '_getUrl'
        ];
		$functions = get_class_methods($this->config);
        $data = [];
        
        foreach ($functions as $methodName) {
            
            if (!in_array($methodName, $notRequired)) {
                $data[substr($methodName, 2)] = $this->config->$methodName($scopeId, $scopeType);
            }
        }
        $data = $this->escapeAvaTaxData($data);
        return $data;
    }

    /**
     * escapeAvaTaxData function
     *
     * @param array $data
     * @return array
     */
    public function escapeAvaTaxData(array $data)
    {
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $key=>&$value) {
            if (is_array($value)) {
                $value = $this->escapeAvaTaxData($value);
            } else {
                /* To Exclude html value from log */
                if (preg_match(self::HTML_ESCAPE_PATTERN, $value)) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * getLatencyTimeAndConnectorTime function
     *
     * @param array $logContext
     * @return array
     */
    public function getLatencyTimeAndConnectorTime(array $logContext)
    {
        $connectorTime = null;
        $latencyTime = null;
        $isSufficientData = 0;
        if (empty($logContext)) {
            return [$connectorTime, $latencyTime];
        }
        if (isset($logContext['ConnectorTime']['start'])) {
            $isSufficientData++;
        }
        if (isset($logContext['ConnectorTime']['end'])) {
            $isSufficientData++;
        }
        if (isset($logContext['ConnectorLatency']['start'])) {
            $isSufficientData++;
        }
        if (isset($logContext['ConnectorLatency']['end'])) {
            $isSufficientData++;
        }
        if ($isSufficientData < 4) {
            return [$connectorTime, $latencyTime];
        }
        $executionTime = $logContext['ConnectorTime']['end'] - $logContext['ConnectorTime']['start'];
        $latencyTime = $logContext['ConnectorLatency']['end'] - $logContext['ConnectorLatency']['start'];
        if ($latencyTime < 0) {
            $latencyTime = 0;
        }
        $connectorTime = $executionTime - $latencyTime;
        return [$connectorTime, $latencyTime];
    }

    /**
     * TransactionDebug API Logging
     *
     * @param array $logContext
     * @param $scopeId
     * @param $scopeType
     * @return void
     */
    public function makeTransactionDebugLog(array $logContext, $scopeId, $scopeType)
    {
        try {
            $message = isset($logContext['message']) ? $logContext['message'] : 'Debug Log : Exception Occured.';
            if (isset($logContext['extra']['amount'])) {
                $message .= " Total: ".$logContext['extra']['amount'];
            }
            if (isset($logContext['extra']['tax'])) {
                $message .= " Tax: ".$logContext['extra']['tax'];
            }
            $source = isset($logContext['source']) ? $logContext['source'] : 'TransactionPage';
            $operation = isset($logContext['operation']) ? $logContext['operation'] : 'TransactionOperation';
            $logType = \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_TYPE_DEBUG;
            $logLevel = isset($logContext['log_level']) ? $logContext['log_level'] : \Avalara\Excise\BaseProvider\Helper\Generic\Config::API_LOG_LEVEL_EXCEPTION;
            $functionName = isset($logContext['function_name']) ? $logContext['function_name'] : __METHOD__;
            if (isset($logContext['extra']['ConnectorTime']) && isset($logContext['extra']['ConnectorLatency']) ) {
                list($connectorTime, $latencyTime) = $this->getLatencyTimeAndConnectorTime($logContext['extra']);
                unset($logContext['extra']['ConnectorTime']);
                unset($logContext['extra']['ConnectorLatency']);
                if (!is_null($connectorTime))
                    $logContext['extra']['ConnectorTime'] = intval($connectorTime * 1000);
                if (!is_null($latencyTime))
                    $logContext['extra']['ConnectorLatency'] = intval($latencyTime * 1000);
            }
            $context = [
                'config' => [
                    'source' => $source,
                    'operation' => $operation,
                    'log_type' => $logType,
                    'log_level' => $logLevel,
                    'function_name' => $functionName,
                    'extra_params' => isset($logContext['extra']) ? $logContext['extra'] : []
                ]
            ];
            $this->apiLog($message, $context, $scopeId, $scopeType);
        } catch(\Exception $e) {
            //do nothing as this is internal logging
        }
    }
}
