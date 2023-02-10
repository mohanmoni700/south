<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin;

/**
 * af_bv_op
 */
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

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
     * @var Http $request
     */
    private $request;

    /**
     * @param MessageManager $messageManager
     * @param SalesOrderGridCollection $collection
     * @param Http $request
     */
    public function __construct(
        MessageManager $messageManager,
        SalesOrderGridCollection $collection,
        Http $request
    ) {
        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->request = $request;
    }

    /**
     * Around Get Report
     *
     * @param CollectionFactory $subject
     * @param \Closure $proceed
     * @param string $requestName
     * @return mixed
     */
    public function aroundGetReport(
        CollectionFactory $subject,
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
            $result->addFilterToMap('store_id', 'main_table.store_id');
            $result->addFilterToMap('subtotal', 'main_table.subtotal');

            return $result;
        }

        return $result;
    }
}
