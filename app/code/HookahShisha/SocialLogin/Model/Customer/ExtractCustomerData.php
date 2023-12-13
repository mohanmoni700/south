<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount as MagentoCreateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData as MagentoExtractCustomerData;
use HookahShisha\SocialLogin\Helper\Customer as CustomerHelper;
use HookahShisha\SocialLogin\Model\Config;

class ExtractCustomerData
{
    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var CustomerHelper
     */
    private CustomerHelper $customerHelper;

    /**
     * Google constructor.
     * @param Config $config
     * @param CustomerHelper $customerHelper
     */
    public function __construct(
        Config $config,
        CustomerHelper $customerHelper
    ) {
        $this->config = $config;
        $this->customerHelper = $customerHelper;
    }

    /**
     * Get isSocial attribute value
     *
     * @param MagentoExtractCustomerData $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @return mixed
     */
    public function aroundExecute(
        MagentoExtractCustomerData $subject,
        callable $proceed,
        CustomerInterface $customer
    ) {
        $result = $proceed($customer);
        try {
            $customerIsSocial = $customer->getCustomAttribute('is_social');
            if (isset($customerIsSocial)) {
                $result['is_social_login'] = $customer->getCustomAttribute('is_social')->getValue();
            } else {
                $result['is_social_login'] = 0;
            }
        } catch (\Exception $exception) {
            $result['is_social_login'] = 0;
        }
        return $result;
    }
}
