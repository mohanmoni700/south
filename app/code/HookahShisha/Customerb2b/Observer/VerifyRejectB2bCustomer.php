<?php
declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Observer;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer for adminhtml_customer_save_after event. Sent email to account verified and reject
 */
class VerifyRejectB2bCustomer implements ObserverInterface
{
    /**
     * Customer converter
     *
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core model store manager interface
     *
     * @var \HookahShisha\Customerb2b\Helper\Data
     */
    protected $helperb2b;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRegistry $customerRegistry
     * @param \HookahShisha\Customerb2b\Helper\Data $helperb2b
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerRegistry $customerRegistry,
        \HookahShisha\Customerb2b\Helper\Data $helperb2b
    ) {
        $this->_storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->helperb2b = $helperb2b;
    }

    /**
     * Update verified or rejected for customer, send notification
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $data = $request->getPost('customer');

        $customer = $observer->getEvent()->getCustomer();
        $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
        if ($data && $companyId && $data['is_sentmail'] == 1) {

            $cst_account_verified = $customer->getCustomAttribute('cst_account_verified');
            $cstAccountVerified = $cst_account_verified ? $cst_account_verified->getValue() : 0;
            $cst_details_changed = $customer->getCustomAttribute('cst_details_changed');
            $cstDetailsChanged = $cst_details_changed ? $cst_details_changed->getValue() : 0;

            $cst_verification_message = $customer->getCustomAttribute('cst_verification_message');
            $cstVerificationMessage = $cst_verification_message ? $cst_verification_message->getValue() : "";
            if (empty($cstVerificationMessage)) {
                $cstVerificationMessage = "Some Of your details has been rejected. please update the same";
            }

            $data['email'] = $customer->getEmail();
            $data['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            $data['rejectReason'] = $cstVerificationMessage;
            $data['store_id'] = $customer->getStoreId();
            $data['b2bformtype'] = "Basic Details";

            $isCstCom = "";
            $status = 0;
            if ($customer->getStoreId() == 8) {
                if ($cstAccountVerified == 0 && $cstDetailsChanged == 0) {
                    $isCstCom = "reject";
                    $this->helperb2b->sendRejectEmail($isCstCom, $data, $status);
                }
                if ($cstAccountVerified == 1 && $cstDetailsChanged == 0) {
                    $isCstCom = "approve";
                    $this->helperb2b->sendRejectEmail($isCstCom, $data, $status);
                }
            }
        }
    }
}
