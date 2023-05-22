<?php


namespace Avalara\Excise\Controller\Adminhtml\CompanyCodes;

use Avalara\Excise\Exception\AvalaraConnectionException;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Framework\Rest\Company;
use Avalara\Excise\Helper\Config;
use Avalara\Excise\Framework\Constants;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Avalara\Excise\Framework\Rest\ListEntityUseCodes;
use Avalara\Excise\Model\EntityUseCodeFactory;
use Avalara\Excise\Framework\Rest;

/**
 * @codeCoverageIgnore
 */
class Get extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultPageFactory;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var Avalara\Excise\Model\EntityUseCodeFactory
     */
    protected $entityUseCodeFactory;

    /**
     * @var ListEntityUseCodes
     */
    protected $entityUseCodes;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var DataObjectFactory
     */
    protected $dataObject;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory
     * @param Company $company
     * @param Config $config
     * @param DataObjectFactory $dataObject
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultPageFactory,
        Company $company,
        Config $config,
        DataObjectFactory $dataObject,
        LoggerInterface $logger,
        ListEntityUseCodes $entityUseCodes,
        EntityUseCodeFactory $entityUseCodeFactory,
        Rest $exciseClient
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->company = $company;
        $this->config = $config;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->entityUseCodes = $entityUseCodes;
        $this->_entityUseCodeFactory = $entityUseCodeFactory;
        $this->exciseClient = $exciseClient;
    }

    /**
     * {@inheritDoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::config_tax');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $obj = $this->dataObject->create();
        $companies = [];
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $postValue = $request->getPostValue();
        $isSandbox = (bool)$request->getParam('mode');
        $apiType = $request->getParam('api_type');
        $resultJson = $this->resultPageFactory->create();
        $scope = isset($postValue['scope']) ? $postValue['scope'] : 0;
        $scopeType = $postValue['scope_type'] === 'global' ?
        ScopeInterface::SCOPE_STORE : $postValue['scope_type'];
        $currentCompanyId = $apiType == Constants::EXCISE_API ?
        $this->config->getExciseCompanyId($scope, $scopeType) :
        $this->config->getAvataxCompanyId($scope, $scopeType);

        $obj = $this->dataObject->create();
        $obj->setData('filter', "isActive eq 'true'");
        try {
            if (!isset($postValue['license_key'])) {
                $postValue['license_key'] = $apiType == Constants::EXCISE_API ?
                $this->config->getExciseLicenseKey($scope, $scopeType) :
                $this->config->getAvaTaxLicenseKey($scope, $scopeType);
            }

            $type = Constants::AVALARA_API;
            $entityObj = $this->dataObject->create();
            $entityUseCodesResponse = $this->entityUseCodes->getEntityUseCodesListWithSecurity(
                $type,
                $postValue['account_number'],
                $postValue['license_key'],
                $entityObj,
                $isSandbox,
                $scope,
                $scopeType
            );

            if($entityUseCodesResponse){
                //Code to insert entity use codes from API response
                $result = $this->_entityUseCodeFactory->create();
                $collection = $result->getCollection();
                $tableName = $collection->getResource()->getMainTable();
                $conn = $collection->getConnection();

                // truncate the table first
                $conn->truncateTable($tableName);
                // Create data to insert into teh table
                $insertArray = $this->getInsertData($entityUseCodesResponse);
                $conn->insertMultiple($tableName, $insertArray);
            }

            $companies = $this->company->getCompaniesWithSecurity(
                $apiType,
                $postValue['account_number'],
                $postValue['license_key'],
                $obj,
                $isSandbox,
                $scope,
                $scopeType
            );

            

        } catch (AvalaraConnectionException $e) {
            // If for any reason we couldn't get any companies, just ignore and no companies will be returned
            // code to add CEP logs for exception
            try {
                $functionName = "getexecute";
                $operationName = "Framework_Interaction_Rest_Company";
                $source = "avatax_companies";
                // @codeCoverageIgnoreStart
                $this->logger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e,
                    $source,
                    $scope,
                    $scopeType
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
            $companies = [];
            $this->logger->critical($e->getMessage());
        }

        if (count($companies) == 0) {
            return $resultJson->setData(
                [
                    'companies' => [],
                    'current_id' => $currentCompanyId
                ]
            );
        }

        if ($apiType == Constants::EXCISE_API) {
            
            $companyData = array_map(
                function ($company) {
                    /** Start : Remove Non-Inclusive Term */
                    $mainBranchPart1 = "mast";
                    $mainBranchPart2 = "er";
                    $mainCompanyIdParamName = $mainBranchPart1 . $mainBranchPart2 . "_company_id";
                    /** End : Remove Non-Inclusive Term */
                    /** @var DataObject $company */
                    return [
                        'company_id' => $company->getData($mainCompanyIdParamName),
                        'company_code' => null,
                        'name' => $company->getData('name'),
                    ];
                },
                $companies
            );
        } else {
            $companyData = array_map(
                function ($company) {
                    /** @var DataObject $company */
                    return [
                        'company_id' => $company->getData('id'),
                        'company_code' => $company->getData('company_code'),
                        'name' => $company->getData('name'),
                    ];
                },
                $companies
            );
        }

        return $resultJson->setData(
            [
                'companies' => $companyData,
                'current_id' => $currentCompanyId
            ]
        );
    }

    /**
     * get data entity use code 
     *
     * @param   array   $entityUseCodesResponse
     *
     * @return  $optionArr
    */
    private function getInsertData($entityUseCodesResponse)
    {
        foreach ($entityUseCodesResponse as $value) {
            $optionArr[] = [
                'code' => $value['code'],
                'name' => $value['code'] .' - '. $value['name']                 
            ];
        }
        return $optionArr;
    }
}
