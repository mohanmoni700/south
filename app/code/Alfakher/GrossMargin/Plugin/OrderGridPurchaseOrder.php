<?php

namespace Alfakher\GrossMargin\Plugin;

/**
 *
 */
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;

class OrderGridPurchaseOrder {

	private $messageManager;
	private $collection;

	public function __construct(
		MessageManager $messageManager,
		SalesOrderGridCollection $collection,
		\Magento\Framework\App\Request\Http $request
	) {
		$this->messageManager = $messageManager;
		$this->collection = $collection;
		$this->request = $request;
	}

	public function aroundGetReport(
		\Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
		\Closure $proceed,
		$requestName
	) {
		$filters = $this->request->getParams();
		$result = $proceed($requestName);
		if ($requestName == 'sales_order_grid_data_source') {
			$result->getSelect()->joinLeft(
				["purchaseOrderTable" => $this->collection->getTable("sales_order")],
				'main_table.entity_id = purchaseOrderTable.entity_id',
				['purchase_order']
			);
			$result->addFilterToMap('purchase_order', 'purchaseOrderTable.purchase_order');
			return $result;
		}
		return $result;
	}
}