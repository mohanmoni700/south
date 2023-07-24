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
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Store\Api\Data\StoreInterface;

class CreateCustomerAccount
{
    /**
     *
     * @param MagentoCreateCustomerAccount $subject
     * @param callable $proceed
     * @param $data
     * @param StoreInterface $store
     * @return CustomerInterface |null
     */
    public function aroundExecute(
        MagentoCreateCustomerAccount $subject,
        callable $proceed,
        $data,
        StoreInterface $store
    ): CustomerInterface {
        /**
         * @var $customer CustomerInterface
         */
            $customer = $proceed($data, $store);

        if ($customer && isset($data['is_social'])) {
            $customer->setCustomAttribute('is_social', Boolean::VALUE_YES);
        }

        return $customer;
    }

}
