<?php
declare (strict_types = 1);

namespace HookahShisha\Checkoutchanges\Model\Resolver;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Resolver for fetching last payment method
 */
class GetCustomerLastPaymentMethod implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * GetCustomerLastPaymentMethod constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        OrderRepositoryInterface $orderRepository,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->orderRepository = $orderRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        if ($cart->getCustomerId()) {
            $filter = [
                $this->filterBuilder->setField('customer_id')
                    ->setValue($cart->getCustomerId())
                    ->setConditionType('eq')
                    ->create(),
            ];
            $sortOrder = $this->sortOrderBuilder->setField('entity_id')->setDirection(SortOrder::SORT_DESC)->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilters($filter)
                ->setSortOrders([$sortOrder])
                ->setCurrentPage(1)
                ->setPageSize(1)
                ->create();
            $orders = $this->orderRepository->getList($searchCriteria)->getItems();
            if (count($orders)) {
                foreach ($orders as $order) {
                    return $order->getPayment()->getMethod();
                }
            }
        }
    }
}
