<?php

namespace Magedelight\SubscribenowGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Api\ProductSubscribersRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Subscriptions implements ResolverInterface
{
    /**
     * @var ProductSubscribersRepositoryInterface
     */
    private $subscribersRepository;

    public function __construct(ProductSubscribersRepositoryInterface $subscribersRepository)
    {
        $this->subscribersRepository = $subscribersRepository;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var \Magento\GraphQl\Model\Query\ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $subscriptionId = $args['subscriptionId'];
        try {
            $subscription = $this->subscribersRepository->getById($subscriptionId);
        } catch (\Exception $exception) {
            throw new GraphQlAuthorizationException(__($exception->getMessage()));
        }
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
