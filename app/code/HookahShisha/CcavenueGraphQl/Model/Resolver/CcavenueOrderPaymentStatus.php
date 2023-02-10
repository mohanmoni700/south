<?php
declare(strict_types=1);

namespace HookahShisha\CcavenueGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CcavenueOrderPaymentStatus implements ResolverInterface
{

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * CcavenueOrderPaymentStatus constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository       = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }//end __construct()

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $incrOrderId = $this->getOrderId($args);
        $orderId     = $this->getOrderIdByIncrementId($incrOrderId);

        $salesData = $this->getOrderData($orderId);

        return $salesData;
    }//end resolve()

    /**
     * Check if order id has been passed or not.
     *
     * @param  array $args
     */
    private function getOrderId(array $args): string
    {
        if (!isset($args['orderId'])) {
            throw new GraphQlInputException(
                __('"order id should be specified')
            );
        }

        return $args['orderId'];
    }//end getOrderId()

    /**
     * Get order data using order id.
     *
     * @param  int $orderId
     */
    private function getOrderData(int $orderId): array
    {
        try {
            $order     = $this->orderRepository->get($orderId);
            $orderData = [
                'status' => $order->getStatus(),
            ];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $orderData;
    }//end getOrderData()

    /**
     * Get order is by increment id.
     *
     * @param  int $incrementId
     */
    public function getOrderIdByIncrementId($incrementId)
    {
        $orderId = null;
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
            $orderData      = $this->orderRepository->getList($searchCriteria)->getItems();
            foreach ($orderData as $order) {
                $orderId = (int) $order->getId();
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $orderId;
    }//end getOrderIdByIncrementId()
}//end class
