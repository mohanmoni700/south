<?php

namespace Avalara\Excise\Controller\Adminhtml\System\Config\Validate;

use Magento\Framework\Controller\Result\JsonFactory;
use Avalara\Excise\Api\RestInterface;
use Avalara\Excise\Framework\Rest\Company;
use Avalara\Excise\Framework\Constants;
use Magento\Framework\App\RequestInterface;
use Avalara\Excise\Helper\Config;
use Psr\Log\LoggerInterface;
use Avalara\Excise\Api\Rest\LoggerApiInterface;
use Magento\Store\Model\ScopeInterface;
use Avalara\Excise\Exception\AvalaraConnectionException;

class Validateexciselicense extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RestInterface
     */
    protected $restFramework;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param RestInterface $restFramework
     * @param Company $company
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        RestInterface $restFramework,
        Company $company,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->restFramework = $restFramework;
        $this->company = $company;
        $this->request = $request;
        $this->helperConfig = $config;
        $this->logger = $logger;
    }

    /**
     * Check whether credentials are valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $accountNumber = $this->request->getParam('account_number', null);
        $key = $this->request->getParam('licence_key', null);
        $mode = $this->request->getParam('mode', null);
        $scopeId = $this->request->getParam('scope_id');
        $scopeType = $this->request->getParam('scope_type');
        $scopeType = $scopeType === 'global' ? ScopeInterface::SCOPE_STORE : $scopeType;
        $scopeId = empty($scopeId) ? 0 : $scopeId;
        $type = Constants::EXCISE_API;
        $request = null;

        $pattern = "/^[*]+$/";
        if (preg_match($pattern, $key)) {
            $key = $this->helperConfig->getExciseLicenseKey($scopeId, $scopeType);
        }
        try {
            $res = $this->company->getCompaniesWithSecurity(
                $type,
                $accountNumber,
                $key,
                $request,
                $mode,
                $scopeId,
                $scopeType
            );
        } catch (AvalaraConnectionException $e) {
            // If for any reason we couldn't get any companies, just ignore and no companies will be returned
            $res = false;
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $this->logPing($scopeId, $scopeType);
        if ($res) {
            return $resultJson->setData([
                'valid' => 1,
                'message' => __('Excise API connection successful.'),
            ]);
        }
        return $resultJson->setData([
            'valid' => 0,
            'message' => __('Excise API connection un-successful. Please check the credentials'),
        ]);
    }

    /**
     * Call Test connection log
     *
     * @param int $scopeId
     * @param string|null $scopeType
     * @return void
     */
    protected function logPing($scopeId, $scopeType)
    {
        $obj = $this->restFramework->getDataObject();
        $obj->setLogType(LoggerApiInterface::LOG_TYPE['config']);
        $obj->setLogLevel(LoggerApiInterface::LOG_LEVEL['info']);
        $obj->setFunctionName(__METHOD__);
        $this->logger->pingLog(
            $obj,
            $scopeId,
            $scopeType
        );
    }
}
