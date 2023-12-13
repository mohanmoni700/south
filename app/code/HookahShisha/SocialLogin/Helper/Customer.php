<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

class Customer
{
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var CreateCustomerAccount
     */
    private CreateCustomerAccount $createCustomerAccount;
    /**
     * @var Random
     */
    private Random $random;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Customer constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CreateCustomerAccount $createCustomerAccount
     * @param Random $random
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CreateCustomerAccount $createCustomerAccount,
        Random $random,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->createCustomerAccount = $createCustomerAccount;
        $this->random = $random;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $data
     * @param string|null $password
     * @param int $generatedPasswordLength
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function createOrGetCustomer(
        array $data,
        string $password = null,
        int $generatedPasswordLength = 16
    ): CustomerInterface {
        try {
            $customer = $this->customerRepository->get($data['email']);
        } catch (LocalizedException $localizedException) {
            if (!$password) {
                $password = $this->random->getRandomString($generatedPasswordLength);
            }
            $data['password'] = $password;
            $data['is_social'] =Boolean::VALUE_YES;
            $customer = $this->createCustomerAccount->execute($data, $this->storeManager->getStore());
        }
        return $customer;
    }
}
