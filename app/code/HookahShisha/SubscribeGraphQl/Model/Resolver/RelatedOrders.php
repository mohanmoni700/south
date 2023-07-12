<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociateOrders;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;

class RelatedOrders implements ResolverInterface
{
    /**
     * @var AssociateOrders
     */
    protected AssociateOrders $associateOrders;

    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @param AssociateOrders $associateOrders
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        AssociateOrders $associateOrders,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->associateOrders = $associateOrders;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $subscriptionId = $args['subscriptionId'];
        $data = [];
        try {
            $orders = $this->getRelatedOrders($subscriptionId, $args);
            $items = [];
            if (count($orders)) {
                foreach ($orders as $order) {
                    $items[] = [
                        'increment_id' => $order->getIncrementId(),
                        'order_date' => $order->getCreatedAt(),
                        'grand_total' => [
                            "value" => $order->getGrandTotal(),
                            "currency" => $order->getOrderCurrencyCode(),
                        ],
                        'status' => $order->getStatusLabel(),
                        'firstname' => $order->getShippingAddress()->getFirstname(),
                        'lastname' => $order->getShippingAddress()->getLastname(),
                    ];
                }
            }
            $data = [
                "total_count" => count($orders),
                "items" => $items,
            ];
        } catch (\Exception $exception) {
            throw new GraphQlAuthorizationException(__($exception->getMessage()));
        }
        return $data;
    }

    /**
     * Fetch list of related orders
     *
     * @param Int $subscriptionId
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function getRelatedOrders($subscriptionId, $params)
    {
        try {
            $filter = [
                $this->filterBuilder->setField('increment_id')
                    ->setValue($this->getAssociateOrder($subscriptionId))
                    ->setConditionType('in')
                    ->create(),
            ];
            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection(SortOrder::SORT_DESC)->create();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilters($filter)
                ->setSortOrders([$sortOrder])
                ->setCurrentPage($params['currentPage'])
                ->setPageSize($params['pageSize'])
                ->create();
            $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        } catch (\Exception $exception) {
            throw new GraphQlAuthorizationException(__($exception->getMessage()));
        }
        return $orders;
    }

    /**
     * Get order's increment ids
     *
     * @param Int $subscriptionId
     * @return array
     */
    public function getAssociateOrder($subscriptionId)
    {
        $collection = $this->associateOrders->create()
            ->addFieldToFilter('subscription_id', $subscriptionId);
        return ($collection->getSize() > 0) ? $collection->getColumnValues('order_id') : [];
    }
}
