<?php

namespace Alfakher\GrossMargin\Plugin;

/**
 * af_bv_op
 */
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;

class OrderGridPurchaseOrder
{
    /**
     * @var MessageManager $messageManager
     */
    private $messageManager;

    /**
     * @var SalesOrderGridCollection $collection
     */
    private $collection;

    /**
     * @param MessageManager $messageManager
     * @param SalesOrderGridCollection $collection
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        MessageManager $messageManager,
        SalesOrderGridCollection $collection,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->request = $request;
    }

    /**
     * Around Get Report
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Closure $proceed
     * @param string $requestName
     */
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

        if ($requestName == 'sales_order_invoice_grid_data_source') {
            $result->getSelect()->joinLeft(
                ["purchaseOrderTable" => $this->collection->getTable("sales_invoice")],
                'main_table.entity_id = purchaseOrderTable.entity_id',
                ['purchase_order']
            );
            $result->addFilterToMap('purchase_order', 'purchaseOrderTable.purchase_order');
            return $result;
        }

        return $result;
    }
}
