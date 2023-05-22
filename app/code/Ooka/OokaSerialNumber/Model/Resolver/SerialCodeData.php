<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Model\Resolver;

use Ooka\OokaSerialNumber\Model\Api\SerialNumberRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

class SerialCodeData implements ResolverInterface
{
    /**
     * @var SerialNumberRepository
     */
    private $serialNumberRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SerialNumberRepository $serialNumberRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SerialNumberRepository $serialNumberRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->serialNumberRepository = $serialNumberRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Reslover for send all data to frontend
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $criteria = $this->searchCriteriaBuilder->create();
        $items = $this->serialNumberRepository->getList($criteria)->getItems();
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                "id" => $item->getId(),
                "order_id" => $item->getOrderId(),
                "sku" => $item->getSku(),
                "serial_code" => $item->getSerialCode(),
                "shipment_type" => $item->getShipmentType(),
                'customer_email' => $item->getCustomerEmail(),
            ];
        }
        return $data;
    }
}
