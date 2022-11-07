<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as Subject;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\App\Request\Http;

class SalesInvoiceCustomColumn
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
    protected $request;

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
     * Around exitb Number
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @param string $requestName
     *
     * @return Collection
     * @throws \Exception
     */
    public function aroundGetReport(
        Subject $subject,
        \Closure $proceed,
        $requestName
    ) {
        $filters = $this->request->getParams();
        $result = $proceed($requestName);

        if ($requestName == 'sales_order_invoice_grid_data_source') {
            $result->getSelect()->joinLeft(
                ["exitb_number" => $this->collection->getTable("sales_invoice")],
                'main_table.entity_id = exitb_number.entity_id',
                ['exitb_invoice_numbers']
            );
            return $result;
        }
        return $result;
    }
}
