<?php

namespace Magedelight\SubscribenowGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Api\ProductSubscribersRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GenerateSubscriptionByOrderId implements ResolverInterface
{
    /**
     * @var ProductSubscribersRepositoryInterface
     */
    private $productSubscribersRepository;

    public function __construct(ProductSubscribersRepositoryInterface $productSubscribersRepository)
    {
        $this->productSubscribersRepository = $productSubscribersRepository;
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
        if (empty($args['orderId'])) {
            throw new GraphQlInputException(__('Specify the "orderId" value.'));
        }
        $orderId = $args['orderId'];
        $result = [];
        try {
            $response = $this->productSubscribersRepository->createByOrderId($orderId);
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
