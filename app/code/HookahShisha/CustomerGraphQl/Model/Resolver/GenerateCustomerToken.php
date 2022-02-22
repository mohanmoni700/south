<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    CORRA
 */
declare(strict_types=1);

namespace HookahShisha\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class GenerateCustomerToken implements ResolverInterface
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService,
        CustomerFactory $customerFactory
    ) {
        $this->customerTokenService = $customerTokenService;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (empty($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }

        try {
            $websiteId = $context->getExtensionAttributes()->getStore()->getWebsiteId();
            $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($args['email']);
            $migrateCustomer = $customer->getMigrateCustomer();

            if ($customer->getId() && $migrateCustomer) {
                return [
                    'token' => '',
                    'reset_password' => true
                ];
            }

            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);

            return [
                'token' => $token,
                'reset_password' => false
            ];
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
}
