<?php
declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Observer;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer for adminhtml_company_save_after event. Sent email to account verified and reject.
 */
class VerifyRejectB2bCompany implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \HookahShisha\Customerb2b\Helper\Data
     */
    protected $helperb2b;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * AfterCompanySaveObserver constructor.
     *
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \HookahShisha\Customerb2b\Helper\Data $helperb2b
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \HookahShisha\Customerb2b\Helper\Data $helperb2b,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->localeResolver = $localeResolver;
        $this->helperb2b = $helperb2b;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Update verified or rejected for company, send notification
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $companyData = $observer->getCompany();

        if ($companyData->getComAccountVerified() == 1) {
            $companyData->setComDetailsChanged(0);
        } elseif (!empty($companyData->getComVerificationMessage())) {
            $companyData->setComDetailsChanged(0);
        }
        $this->companyRepository->save($companyData);
    }
}
