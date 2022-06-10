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
            $result->addFilterToMap('entity_id', 'main_table.entity_id');
            $result->addFilterToMap('created_at', 'main_table.created_at');
            $result->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
            $result->addFilterToMap('grand_total', 'main_table.grand_total');
            $result->addFilterToMap('increment_id', 'main_table.increment_id');
            $result->addFilterToMap('state', 'main_table.state');
            $result->addFilterToMap('sales_tax', 'purchaseOrderTable.sales_tax');
            $result->addFilterToMap('excise_tax', 'purchaseOrderTable.excise_tax');
            $result->addFilterToMap('base_shipping_tax_amount', 'purchaseOrderTable.base_shipping_tax_amount');

            return $result;
        }

        return $result;
    }
}
