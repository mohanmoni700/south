<?php

namespace Alfakher\OfflinePaymentRecords\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;

class OrderGridOfflinePaymentRecords
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
                ["paymentRecordsTable" => $this->collection->getTable("sales_order")],
                'main_table.entity_id = paymentRecordsTable.entity_id',
                ['offline_payment_type', 'offline_transaction_date']
            );
            $result->addFilterToMap('offline_payment_type', 'paymentRecordsTable.offline_payment_type');
            $result->addFilterToMap('offline_transaction_date', 'paymentRecordsTable.offline_transaction_date');
            return $result;
        }

        return $result;
    }
}
