<?php

namespace Avalara\Excise\Controller\Adminhtml\System\Config\Validate;

use Magento\Framework\Controller\Result\JsonFactory;
use Avalara\Excise\Api\RestInterface;
use Avalara\Excise\Framework\Rest\Company;
use Avalara\Excise\Framework\Constants;
use Magento\Framework\App\RequestInterface;
use Avalara\Excise\Helper\Config;
use Magento\Store\Model\ScopeInterface;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Avalara\Excise\Helper\ApiLog;

/**
 * @codeCoverageIgnore
 */

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
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param RestInterface $restFramework
     * @param Company $company
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param Config $config
     * @param ApiLog $apiLog
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        RestInterface $restFramework,
        Company $company,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        Config $config,
        ApiLog $apiLog
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->restFramework = $restFramework;
        $this->company = $company;
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
        $message = 'Excise API connection un-successful. Please check the credentials';
        $isValid = 0;
        if ($res) {
            $message = 'Excise API connection successful.';
            $isValid = 1;
        }
        $this->apiLog->testConnectionLog($message, $scopeId, $scopeType);

        return $resultJson->setData([
            'valid' => $isValid,
            'message' => __($message),
        ]);
    }
}
