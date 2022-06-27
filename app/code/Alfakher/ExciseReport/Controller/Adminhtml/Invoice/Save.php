<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Invoice;

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
        si.increment_id AS InvoiceNumber,
        si.created_at AS InvoiceDate,
        si.state AS Status,
        sp.last_trans_id AS TransactionId,
        so.increment_id AS OrderNumber,
        so.created_at AS OrderDate,
        si.subtotal AS SubTotal,
        si.grand_total  AS GrandTOtal,
        si.sales_tax AS SalesTax,
        si.excise_tax AS ExciseTax,
        si.shipping_tax_amount AS ShippingTax
        FROM
        sales_invoice AS si
        LEFT JOIN
        sales_order AS so
        ON so.entity_id = si.order_id
        LEFT JOIN
        sales_order_payment AS sp
        ON sp.parent_id = si.order_id
        WHERE
        si.created_at >=  '" . $startdate . "'
        AND si.created_at <= '" . $enddate . "'
        AND si.store_id = '" . $websiteid . "'
        ORDER BY
        si.created_at";

        $values = $connection->fetchAll($query);

        $custom = [];
        foreach ($values as $key => $value) {
            if ($value['Status'] == '1') {
                $value['Status'] = 'Pending';
            } elseif ($value['Status'] == '2') {
                $value['Status'] = 'Paid';
            } elseif ($value['Status'] == '3') {
                $value['Status'] = 'Canceled';
            }
            $custom[] = $value;
        }

        $header = [];
        $header[] = [
            'Invoice number' => 'Invoice number',
            'Invoice Date' => 'Invoice Date',
            'Status' => 'Status',
            'Transaction Id' => 'Transaction Id',
            'Order Number' => 'Order Number',
            'Order Date' => 'Order Date',
            'Sub Total' => 'Sub Total',
            'Grand Total' => 'Grand Total',
            'Sales Tax' => 'Sales Tax',
            'Excise Tax' => 'Excise Tax',
            'Shipping Tax' => 'Shipping Tax',
        ];

        $report = [];
        $report = array_merge($header, $custom);

        $fileName = 'Invoice_Report.csv';
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
