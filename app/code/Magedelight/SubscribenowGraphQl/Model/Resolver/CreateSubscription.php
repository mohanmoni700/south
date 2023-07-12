<?php

namespace Magedelight\SubscribenowGraphQl\Model\Resolver;

use Magedelight\SubscribenowGraphQl\Model\Subscription\CreateSubscriptionProfile;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface;

class CreateSubscription implements ResolverInterface
{
    /**
     * @var CreateSubscriptionProfile
     */
    private $createSubscriptionProfile;

    public function __construct(CreateSubscriptionProfile $createSubscriptionProfile)
    {
        $this->createSubscriptionProfile = $createSubscriptionProfile;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return ProductSubscribersInterface
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = $context->getUserId();

        $subscription = $this->createSubscriptionProfile->execute($args['input'], $customerId);
        if (isset($subscription['order_item_info'])) {
            $subscription['order_item_info'] = json_encode($subscription['order_item_info']);
        }
        if (isset($subscription['order_info'])) {
            $subscription['order_info'] = json_encode($subscription['order_info']);
        }
        if (isset($subscription['additional_info'])) {
            $subscription['additional_info'] = json_encode($subscription['additional_info']);
        }
        if (isset($subscription['subscription_item_info'])) {
            $subscription['subscription_item_info'] = json_encode($subscription['subscription_item_info']);
        }
        return $subscription;
    }
}
