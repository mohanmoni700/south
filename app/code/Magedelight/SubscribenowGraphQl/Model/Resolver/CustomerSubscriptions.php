<?php

namespace Magedelight\SubscribenowGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Model\SubscribeNowRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CustomerSubscriptions implements ResolverInterface
{
    /**
     * @var SubscribeNowRepository
     */
    private $subscribeNowRepository;
    /**
     * @var Http
     */
    private $request;

    public function __construct(
        SubscribeNowRepository $subscribeNowRepository,
        Http $request
    ) {
        $this->subscribeNowRepository = $subscribeNowRepository;
        $this->request = $request;
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
     */public function resolve(
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
        $customerId = $context->getUserId();
        $params = [];
        if (isset($args['currentPage'])) {
            $params['searchCriteria']['currentPage'] = $args['currentPage'];
        }
        if (isset($args['pageSize'])) {
            $params['searchCriteria']['pageSize'] = $args['pageSize'];
        }
        if (isset($args['filter'])) {
            $params['searchCriteria']['filter'] = $args['filter'];
        }
        if (isset($args['sort'])) {
            $params['searchCriteria']['sort'] = $args['sort'];
        }
        $subscriptions = $this->subscribeNowRepository->getSubscriptionsByCustomer($customerId, $params);
        $items = $subscriptions->getItems();
        return [
            'total_count'=>$subscriptions->getTotalCount(),
            'items' => $items
        ];
    }
}
