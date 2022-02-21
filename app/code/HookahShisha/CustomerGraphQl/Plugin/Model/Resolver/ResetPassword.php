<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    CORRA
 */
declare(strict_types=1);

namespace HookahShisha\CustomerGraphQl\Plugin\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Resolver\ResetPassword as SourceResetPassword;

class ResetPassword
{
    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Set migrate_customer to false after password change
     *
     * @param SourceResetPassword $subject
     * @param false|mixed $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return false|mixed
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterResolve(
        SourceResetPassword $subject, // NOSONAR
        $result,
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null
    ) {
        if (!$result) {
            return false;
        }

        $customer = $this->customerRepository->get($args['email']);

        $customer->setCustomAttribute('migrate_customer', 0);
        $this->customerRepository->save($customer);

        return $result;
    }
}
