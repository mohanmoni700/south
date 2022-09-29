<?php
namespace HookahShisha\Customerb2b\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class EmailNotificationPlugin extends \Magento\Customer\Model\EmailNotification
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    protected $scopeConfig;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     */

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send email with new account related information
     *
     * @param \Magento\Customer\Model\EmailNotification $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string $sendemailStoreId
     * @return void
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subject,
        callable $proceed,
        CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ) {
        $allow = $this->scopeConfig->getValue("hookahshisha/registeremail/enable_mail", ScopeInterface::SCOPE_STORE);

        if ($allow === '1') {
            return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        } else {
            return false;
        }
    }
}
