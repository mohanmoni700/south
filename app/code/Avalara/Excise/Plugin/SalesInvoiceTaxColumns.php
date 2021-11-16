<?php
namespace Avalara\Excise\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

/**
 * Invoice and Creditmemo grid extended collection
 */
class SalesInvoiceTaxColumns
{
    /**
     * @var ResourceConnection
     */
    private $collection;

    /**
     * @param SalesOrderGridCollection $collection
     */
    public function __construct(
        SalesOrderGridCollection $collection
    ) {
        $this->collection = $collection;
    }

    public function aroundGetReport(
        CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {

        $result = $proceed($requestName);

        if ($requestName === 'sales_order_invoice_grid_data_source') {
                $result->getSelect()->join(
                    ["salesInvoice" => $this->collection->getTable("sales_invoice")],
                    'main_table.entity_id = salesInvoice.entity_id',
                    ['sales_tax','excise_tax','base_shipping_tax_amount']
                );
        }

        if ($requestName === 'sales_order_creditmemo_grid_data_source') {
                $result->getSelect()->join(
                    ["salesCreditMemo" => $this->collection->getTable("sales_creditmemo")],
                    'main_table.entity_id = salesCreditMemo.entity_id',
                    ['sales_tax','excise_tax','base_shipping_tax_amount']
                );
        }
        return $result;
    }
}
