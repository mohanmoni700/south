<?php

namespace Avalara\Excise\Framework\Rest;

use Avalara\Excise\Api\Rest\LoggerApiInterface;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Framework\Rest;
use Magento\Framework\DataObject;

class LoggerApi extends Rest implements LoggerApiInterface
{
    /**
     * name of the connector
     */
    const CONNECTOR_NAME = "Magento for Tobacco";

    /**
     * version of the connector
     */
    const CONNECTOR_VERSION = Constants::APP_VERSION;

    /**
     * client info
     */
    const CLIENT_STRING = Constants::CONNECTOR_ID;

    /**
     * logger id
     */
    const CONNECTOR_ID = "a0n5a00000ZmXLNAA3";

    /**
     * ERP details of application
     */
    const ERP_DETAILS = "MAGENTO";
    
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
        $scopeId = 0,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $clientResult = [];
        //$scopeId = 0;
        $host = Constants::ENV_LOGGER_SANDBOX_BASE_URL;
        $accountNumber = $this->config->getAvaTaxAccountNumber($scopeId, $scopeType);
        $currentMode = $this->config->getCurrentModeString($scopeId, $scopeType);
        if ($currentMode == Constants::API_MODE_PROD) {
            $host = Constants::ENV_LOGGER_PRODUCTION_BASE_URL;
        }
        
        $request["CallerAccuNum"] = $accountNumber;
        $request["AvaTaxEnvironment"] = ucwords($currentMode);
        $request["ERPDetails"] = self::ERP_DETAILS;
        $request["ConnectorVersion"] = self::CONNECTOR_VERSION;
        $request["ConnectorName"] = self::CONNECTOR_NAME;
        $request["ClientString"] = self::CLIENT_STRING;

        try {
            $this->isSandboxMode = $host;
            $client = $this->getClient();
            $client = $this->setAvataxCredentials($scopeId, $scopeType, $client);
            $client->withCatchExceptions(false);
            $clientResult = $client->logData($request, self::CONNECTOR_ID, $host);
        } catch (\GuzzleHttp\Exception\RequestException $clientException) {
            $this->handleException($clientException);
        }
        return $this->formatResult($clientResult);
    }

    /**
     * Prepare the test connection request parameters
     *
     * @param DataObject $dataObj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return array
     */
    public function makeTestConnectionRequest(
        DataObject $dataObj,
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $params = [
            "Source" => "ConfigurationPage",
            "Operation" => "Test Connection",
            "Message" => $dataObj->getMessage(),
            "LogType" => $dataObj->getLogType(),
            "LogLevel" => $dataObj->getLogLevel(),
            "FunctionName" => $dataObj->getFunctionName()
        ];
        return $this->sendToLog($params, $scopeId, $scopeType);
    }

    /**
     * Prepare the configuration change request parameters
     *
     * @param DataObject $dataObj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return array
     */
    public function makeConfChangeRequest(
        DataObject $dataObj,
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $params = [
            "Source" => "ConfigurationPage",
            "Operation" => "ConfigChanges",
            "Message" => $dataObj->getMessage(),
            "LogType" => $dataObj->getLogType(),
            "LogLevel" => $dataObj->getLogLevel(),
            "FunctionName" => $dataObj->getFunctionName()
        ];
        return $this->sendToLog($params, $scopeId, $scopeType);
    }

    /**
     * Prepare the transaction log request parameters
     *
     * @param DataObject $dataObj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return array
     */
    public function makeTransactionRequest(
        DataObject $dataObj,
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $params = [
            "Source" => $dataObj->getSource(),
            "Operation" => $dataObj->getOperation(),
            "Message" => $dataObj->getMessage(),
            "LogType" => $dataObj->getLogType(),
            "LogLevel" => $dataObj->getLogLevel(),
            "LineCount" => $dataObj->getLineCount(),
            "FunctionName" => $dataObj->getFunctionName(),
            "EventBlock" => $dataObj->getEventBlock(),
            "DocType" => $dataObj->getDocType(),
            "DocCode" => $dataObj->getDocCode(),
            "ConnectorTime" => $dataObj->getConnectorTime(),
            "ConnectorLatency" => $dataObj->getConnectorLatency()
        ];
        if (is_array($dataObj->getCustom())) {
            $params["custom"] = $dataObj->getCustom();
        }
        return $this->sendToLog($params, $scopeId, $scopeType);
    }

    /**
     * Prepare the debug log request parameters
     *
     * @param DataObject $dataObj
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return array
     */
    public function makeDebugLogRequest(
        DataObject $dataObj,
        $scopeId = null,
        $scopeType = ScopeInterface::SCOPE_STORE
    ) {
        $params = [
            "Source" => $dataObj->getSource(),
            "Operation" => $dataObj->getSource(),
            "Message" => $dataObj->getMessage(),
            "LogType" => $dataObj->getLogType(),
            "LogLevel" => $dataObj->getLogLevel(),
            "FunctionName" => $dataObj->getFunctionName(),
            "EventBlock" => $dataObj->getEventBlock(),
            "StackTrace" => $dataObj->getStackTrace(),
        ];
        return $this->sendToLog($params, $scopeId, $scopeType);
    }
}
