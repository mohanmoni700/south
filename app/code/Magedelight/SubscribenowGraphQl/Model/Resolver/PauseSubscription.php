<?php

namespace Magedelight\SubscribenowGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Api\SubscriptionManagementInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class PauseSubscription implements ResolverInterface
{
    /**
     * @var SubscriptionManagementInterface
     */
    private $subscriptionManagement;

    public function __construct(SubscriptionManagementInterface $subscriptionManagement)
    {
        $this->subscriptionManagement = $subscriptionManagement;
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
        if (empty($args['subscriptionId'])) {
            throw new GraphQlInputException(__('Specify the "subscriptionId" value.'));
        }
        $customerId = $context->getUserId();
        $subscriptionId = $args['subscriptionId'];
        $result = [];
        try {
            $response = $this->subscriptionManagement->pause($subscriptionId, $customerId);
            if (isset($response[0]['success']) && $response[0]['message']) {
                $result['success'] = $response[0]['success'];
                $result['message'] = $response[0]['message'];
            }
        } catch (\Exception $exception) {
            $result['success'] = false;
            $result['message'] = $exception->getMessage();
        }
        return $result;
    }
}
