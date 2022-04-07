<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace HookahShisha\Customerb2b\Controller\Validate;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Validate for the check email exist or not
 */
class Validate extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        // 'email' => $this->isCompanyEmailValid($this->getRequest()->getParam('email')),
        $resultJson->setData([
            'customer_email' => $this->isCustomerEmailValid($this->getRequest()->getParam('customer_email')),
        ]);

        return $resultJson;
    }

    /**
     * Is company email valid
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCompanyEmailValid($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::COMPANY_EMAIL, $email)
            ->create();
        return !$this->companyRepository->getList($searchCriteria)->getTotalCount();
    }

    /**
     * Is customer email valid
     *
     * @param string $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCustomerEmailValid($email): bool
    {
        $websiteId = (int) $this->storeManager->getWebsite()->getId();
        return $this->customerAccountManagement->isEmailAvailable($email, $websiteId);
    }
}
