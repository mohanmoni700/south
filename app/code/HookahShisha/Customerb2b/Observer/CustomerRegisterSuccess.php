<?php
declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer for customer_register_succes event. Set Customer type based on website.
 */
class CustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * Customersave constructor
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Update verified or rejected for company, send notification
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {

        $customer = $observer->getEvent()->getData('customer');
        $customerId = $customer->getId();
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getWebsiteId() == '8') {
            $customer->setCustomAttribute('customer_type', 'WHOLESALE');
            $this->customerRepository->save($customer);
        }
    }
}
