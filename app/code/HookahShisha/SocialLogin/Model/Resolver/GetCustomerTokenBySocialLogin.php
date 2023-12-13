<?php
/**
 *
 * @package     HookahShisha
 * @author      Air global
 * @link        https://air.global/
 */

declare (strict_types=1);
namespace HookahShisha\SocialLogin\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;

class GetCustomerTokenBySocialLogin implements ResolverInterface
{
    /**
     * @var TokenResolverInterface[]
     */
    private array $providers;
    /**
     * @var TokenModelFactory
     */
    private TokenModelFactory $tokenModelFactory;

    /**
     * GetCustomerTokenBySocialLogin constructor.
     * @param TokenModelFactory $tokenModelFactory
     * @param array $providers
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        array $providers = []
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->providers = $providers;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return mixed|Value
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $type = $args['type'] ?? null;
        $token = $args['token'] ?? null;
        if (array_key_exists($type, $this->providers)) {
            $customer = $this->providers[$type]->resolve($token);
            $token = $this->tokenModelFactory->create()->createCustomerToken($customer->getId())->getToken();
            return ['token' => $token];
        }
        throw new LocalizedException(__('No resolver found for type %1', $type));
    }
}
