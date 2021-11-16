<?php

namespace Avalara\Excise\Observer;

use Avalara\Excise\Api\RestInterface;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Avalara\Excise\Helper\Config;
use Magento\Framework\Event\ObserverInterface;
use Avalara\Excise\Helper\ModuleChecks;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Avalara\Excise\Framework\Constants;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Api\Rest\LoggerApiInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class ConfigSaveObserver to check the API credentials
 */
class ConfigSaveObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ModuleChecks
     */
    protected $moduleChecks;

    /**
     * @var RestInterface
     */
    protected $rest;

    /**
     * @var ExciseLogger
     */
    protected $logger;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Config  $config
     * @param ModuleChecks $moduleChecks
     * @param LoggerApiInterface $loggerApi
     * @param DataObjectFactory $dataObjFactory
     * @param RestInterface $rest
     * @param ExciseLogger $logger
     * @param Manager $manager
     * @param WriterInterface $writer
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Config $config,
        ModuleChecks $moduleChecks,
        ExciseLogger $logger,
        DataObjectFactory $dataObjFactory,
        RestInterface $rest,
        Manager $manager,
        WriterInterface $writer
    ) {
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->moduleChecks = $moduleChecks;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjFactory;
        $this->rest = $rest;
        $this->moduleManager = $manager;
        $this->configWriter = $writer;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $scopeId = Store::DEFAULT_STORE_ID;
        $scopeType = ScopeInterface::SCOPE_STORE;

        if ($observer->getStore()) {
            $scopeId = $observer->getStore();
            $scopeType = ScopeInterface::SCOPE_STORE;
        } elseif ($observer->getWebsite()) {
            $scopeId = $observer->getWebsite();
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
        }

        if ($this->moduleManager->isEnabled('ClassyLlama_AvaTax')) {
            $path = Config::XML_PATH_AVALARA_MODULE_ENABLED;
            $value = 0;
            $this->configWriter->save($path, $value, $scopeType, $scopeId);

            $this->messageManager->addError(
                __('You can not use both ClassyLlama_AvaTax and Avalara_Excise module together.')
            );
        }

        foreach ($this->getErrors($scopeId, $scopeType) as $error) {
            $this->messageManager->addError($error);
        }

        foreach ($this->getNotices() as $notice) {
            $this->messageManager->addNotice($notice);
        }

        $this->logConfigChanges($scopeId, $scopeType);

        return $this;
    }

    /**
     * Get all errors that should display when tax config is saved
     *
     * @param $scopeId
     * @param $scopeType
     *
     * @return array
     */
    protected function getErrors($scopeId, $scopeType)
    {
        $errors = [];
        return array_merge(
            $errors,
            $this->pingApi(
                $scopeId,
                $scopeType,
                Constants::EXCISE_API,
                Constants::EXCISE_API_NAME
            ),
            $this->pingApi(
                $scopeId,
                $scopeType,
                Constants::AVALARA_API,
                Constants::AVALARA_API_NAME
            ),
            $this->checkCompanyInfo()
        );
    }

    /**
     * Get all notices  that should display when tax config is saved
     *
     * @return array
     */
    protected function getNotices()
    {
        $notices = [];
        return array_merge(
            $notices,
            // This check is also being displayed at the top of the page via
            // \Avalara\Excise\Model\Message\ConfigNotification, but it's not as visible as a notice message, so
            // also add it as a notice.
            $this->moduleChecks->getModuleCheckErrors()
        );
    }

    /**
     * Ping Excise API
     *
     * @param $scopeId
     * @param $scopeType
     *
     * @return array
     */
    public function pingApi($scopeId, $scopeType, $type, $apiName)
    {
        $errors = [];
        $message = __('Authentication failed');

        if (!$this->config->isModuleEnabled($scopeId, $scopeType)) {
            return $errors;
        }

        $href = "#row_tax_avatax_excise_avatax_excise_heading";
        if ($type == Constants::AVALARA_API) {
            $href = "#row_tax_avatax_excise_avatax_account_number";
        }

        $mode = $this->config->getCurrentModeString();

        try {
            $result = $this->rest->ping(
                null,
                null,
                null,
                $type,
                $scopeId,
                $scopeType
            );

            if ($result) {
                $this->messageManager->addSuccess(
                    __(
                        'Successfully connected to %1 using the ' . '
                        <a href="%2">%3 credentials</a>.',
                        $apiName,
                        $href,
                        $mode
                    )
                );
                $message = "";
            }
        } catch (AvalaraConnectionException $avExp) {
            $message = $avExp->getMessage();
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
        }

        if ($message) {
            $errors[] = __(
                'Error connecting to %1 using the ' . '
                <a href="%2">%3 credentials</a>: %4',
                $apiName,
                $href,
                $mode,
                $message
            );
        }
        return $errors;
    }

    protected function logConfigChanges($scopeId, $scopeType)
    {
        $data = $this->getConfigData($scopeId, $scopeType);
        $obj = $this->dataObjectFactory->create();
        $obj->setLogType(LoggerApiInterface::LOG_TYPE['config']);
        $obj->setLogLevel(LoggerApiInterface::LOG_LEVEL['info']);
        $obj->setFunctionName(__METHOD__);
        $message = "";
        foreach ($data as $key => $value) {
            if (stripos($key, 't') === 0) {
                $key = substr($key, 1);
            }
            $value = empty($value) ? '' : $value;
            $message .= "$key - $value,";
        }
        $obj->setMessage($message);
        $this->logger->configLog($obj, $scopeId, $scopeType);
    }

    private function getConfigData($scopeId, $scopeType)
    {
        $notRequired = [
            '__construct', 'getExciseLicenseKey', 'getAvaTaxLicenseKey',
            'getApplicationName', 'getApplicationDomain', 'getTimeZoneObject',
            'getPriceIncludesTax', 'getOriginAddress'
        ];
        $functions = get_class_methods($this->config);
        $data = [];

        foreach ($functions as $methodName) {
            if (!in_array($methodName, $notRequired)) {
                $data[substr($methodName, 2)] = $this->config->$methodName($scopeId, $scopeType);
            }
        }
        return $data;
    }

    /**
     * Get module check errors
     *
     * @return array
     */
    private function checkCompanyInfo()
    {
        $exciseCmpId = $this->config->getExciseCompanyId();
        $avataxCmpId = $this->config->getAvataxCompanyId();
        return array_merge(
            [],
            empty($exciseCmpId) ? ['Excise Company field is empty'] : [],
            empty($avataxCmpId) ? ['Avatax Company field is empty'] : []
        );
    }
}
