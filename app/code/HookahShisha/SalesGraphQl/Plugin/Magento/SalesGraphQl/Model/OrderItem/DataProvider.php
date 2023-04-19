<?php
declare(strict_types=1);

namespace HookahShisha\SalesGraphQl\Plugin\Magento\SalesGraphQl\Model\OrderItem;

use Magento\SalesGraphQl\Model\OrderItem\DataProvider as DataProviderSubject;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class DataProvider
{
	public function __construct(
		OrderItemRepositoryInterface $orderItemRepository
	){
		$this->orderItemRepository = $orderItemRepository;
	}

	public function afterGetOrderItemById(
		DataProviderSubject $subject,
		$result,
		int $orderItemId
	): array
    {
    	$orderItem = $this->orderItemRepository->get($orderItemId);
    	$result['product_sale_price_inclusive_tax'] = [
    		"value" => $orderItem->getPriceInclTax(),
    		"currency" => $orderItem->getOrder()->getOrderCurrencyCode()
    	];
        return $result;
    }
}