<?php

namespace Avalara\Excise\Controller\Adminhtml\System\Config\Validate;

use Magento\Framework\Controller\Result\JsonFactory;
use Avalara\Excise\Api\RestInterface;
use Magento\Framework\App\RequestInterface;
use Avalara\Excise\Helper\Config;
use Avalara\Excise\Framework\Constants;
use Magento\Store\Model\ScopeInterface;
use Avalara\Excise\Helper\ApiLog;
/**
 * @codeCoverageIgnore
 */
class Validateavataxlicense extends \Magento\Backend\App\Action
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param RestInterface $restFramework
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param Config $config
     * @param ApiLog $apiLog
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        RestInterface $restFramework,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        Config $config,
        ApiLog $apiLog
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->restFramework = $restFramework;
        $this->request = $request;
        $this->helperConfig = $config;
        $this->apiLog = $apiLog;
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
        $type = Constants::AVALARA_API;
        $scopeId = $this->request->getParam('scope_id');
        $scopeType = $this->request->getParam('scope_type');
        $scopeType = $scopeType === 'global' ? ScopeInterface::SCOPE_STORE : $scopeType;
        $scopeId = empty($scopeId) ? 0 : $scopeId;

        $pattern = "/^[*]+$/";
        if (preg_match($pattern, $key)) {
            $key = $this->helperConfig->getAvaTaxLicenseKey($scopeId, $scopeType);
        }
        $res = $this->restFramework->ping($accountNumber, $key, $mode, $type, $scopeId, $scopeType);
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $message = 'Avatax API connection un-successful. Please check the credentials';
        $isValid = 0;
        if ($res) {
            $message = 'Avatax API connection successful.';
            $isValid = 1;
        }
        $this->apiLog->testConnectionLog($message, $scopeId, $scopeType);
        return $resultJson->setData([
            'valid' => $isValid,
            'message' => __($message),
        ]);
    }
}
