<?php
declare (strict_types=1);

namespace Ooka\OokaSerialNumber\Model\Resolver;

use Ooka\OokaSerialNumber\Model\Api\SerialNumberRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class SerialCodeData implements ResolverInterface
{
    /** @var FilterBuilder */
    private $filterBuilder;
    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;
    /** @var SerialNumberRepository */
    private $serialNumberRepository;
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;
    /** @var GetCustomer */
    private $getCustomer;

    /**
     * @param SerialNumberRepository $serialNumberRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        SerialNumberRepository $serialNumberRepository,
        SearchCriteriaBuilder  $searchCriteriaBuilder,
        FilterBuilder          $filterBuilder,
        FilterGroupBuilder     $filterGroupBuilder,
        GetCustomer            $getCustomer
    ) {
        $this->serialNumberRepository = $serialNumberRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->getCustomer = $getCustomer;
    }

    /**
     * Resolver for send all data to frontend
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $pageSize = $args['pageSize'] ?? 10;
        $currentPage = $args['currentPage'] ?? 1;
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customer = $this->getCustomer->execute($context);
        $emailFilter = $this->filterBuilder
            ->setField('main_table.customer_email')
            ->setValue($customer->getEmail())
            ->setConditionType('eq')
            ->create();
        $storeFilter = $this->filterBuilder
            ->setField('thirdTable.store_id')
            ->setValue($storeId)
            ->setConditionType('eq')
            ->create();
        $group1 = $this->filterGroupBuilder->addFilter($emailFilter)->create();
        $group2 = $this->filterGroupBuilder->addFilter($storeFilter)->create();
        $criteria = $this->searchCriteriaBuilder->setFilterGroups([$group1, $group2])->create();
        $items = $this->serialNumberRepository->getList($criteria)->getItems();
        $count = count($items);
        $result = [];
        if ($count) {
            $total_pages =  ceil($count / $pageSize);
            if ($currentPage <= 0 || $currentPage > $total_pages) {
                throw new GraphQlAuthorizationException(__('Invalid Page Number.'));
            }
            $chunks = array_chunk(
                $items,
                $pageSize
            );
            $items = $chunks[$currentPage - 1] ?? $chunks[$currentPage];
            $data = [];
            foreach ($items as $item) {
                $data[] = [
                    "id" => $item->getId(),
                    "order_id" => $item->getOrderId(),
                    "sku" => $item->getSku(),
                    "serial_code" => $item->getSerialCode(),
                    'customer_email' => $item->getCustomerEmail(),
                    'item_id' => $item->getItemId(),
                    'shipment_number' => $item->getShipmentNumber(),
                    'shipping_address' => $item->getShippingAddress(),
                    'website' => $item->getWebsite(),
                    "created_at" => $item->getCreatedAt(),
                    "updated_at" => $item->getUpdatedAt(),
                ];
            }
            $result['record'] = $data;
            $result['total_count'] = $count;
            $result['total_pages'] = $total_pages;
        } else {
            $result['record'] = [];
            $result['total_count'] = 0;
            $result['total_pages'] = 0;
        }
        return $result;
    }
}
