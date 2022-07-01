<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Refund;

/**
 * @OrderExciseReport
 */

class Save extends \Magento\Backend\App\Action
{
    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
    }

    /**
     * Execute
     */
    public function execute()
    {

        $post = $this->getRequest()->getPostValue();
        $startdate = $post['startdate'] . " 00:00:00";
        $enddate = $post['enddate'] . " 23:59:59";
        $websiteid = $post['website'];
        $connection = $this->resourceConnection->getConnection();

        $query = "SELECT
        sc.increment_id AS RefundNumber,
        sc.created_at AS RefundDate,
        sp.last_trans_id AS TransactionId,
        so.increment_id AS OrderNumber,
        so.created_at AS OrderDate,
        sc.base_grand_total AS RefundAmount,
        sc.sales_tax AS SalesTax,
        sc.excise_tax AS ExciseTax,
        sc.base_shipping_tax_amount AS ShippingTax
        FROM
        sales_creditmemo AS sc

        LEFT JOIN
        sales_order AS so
        ON so.entity_id = sc.order_id

        LEFT JOIN
        sales_order_payment AS sp
        ON sp.parent_id = sc.order_id

        WHERE
        sc.created_at >=  '" . $startdate . "'
        AND sc.created_at <= '" . $enddate . "'
        AND sc.store_id = '" . $websiteid . "'
        ORDER BY
        sc.created_at";

        $values = $connection->fetchAll($query);

        $header = [];
        $header[] = [
            'Refund Number' => 'Refund Number',
            'Refund Date' => 'Refund Date',
            'Transaction Id' => 'Transaction Id',
            'Order Number' => 'Order Number',
            'Order date' => 'Order Date',
            'Refund Amount' => 'Refund Amount',
            'Sales Tax' => 'Sales Tax',
            'Excise Tax' => 'Excise Tax',
            'Shipping Tax' => 'Shipping Tax',
        ];

        $report = [];
        $report = array_merge($header, $values);

        $fileName = 'Refund_Report.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;

        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $report
            );

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }
}
