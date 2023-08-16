<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement as MagentoAccountManagement;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Store\Api\Data\StoreInterface;

class AccountManagement
{
    /**
     * @var GetCustomerByToken
     */
    private GetCustomerByToken $getByToken;
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * AccountManagement constructor.
     * @param GetCustomerByToken $getByToken
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        GetCustomerByToken $getByToken,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->getByToken = $getByToken;
        $this->customerRepository = $customerRepository;
    }

    /**
     *
     * @param MagentoAccountManagement $subject
     * @param callable $proceed
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return mixed
     */
    public function aroundResetPassword(
        MagentoAccountManagement $subject,
        callable $proceed,
        $email,
        $resetToken,
        $newPassword
    ) {
        if (!$email) {
            try {
                $customer = $this->getByToken->execute($resetToken);
                $id = $customer->getId();
                $customer->setCustomAttribute('is_social', 0);
                $this->customerRepository->getById($id)->setCustomAttribute('is_social', 0);
                $this->customerRepository->save($customer);
            } catch (\Exception $exception) {
                throw new LocalizedException(
                    __("Could not update Social Sign in information")
                );
            }
        }
        return $proceed($email, $resetToken, $newPassword);
    }
}
