<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Tax;

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
        so.increment_id AS OrderNumber,
        si.increment_id AS InvoiceNumber,
        so.tax_amount AS TotalTax,
        so.sales_tax AS SalesTax,
        so.excise_tax AS ExciseTax,
        so.shipping_tax_amount AS ShippingTax
        FROM
        sales_order AS so

        INNER JOIN
        sales_invoice AS si
        ON si.order_id = so.entity_id

        WHERE
        so.state = 'complete'
        AND so.created_at >=  '" . $startdate . "'
        AND so.created_at <= '" . $enddate . "'
        AND so.store_id = '" . $websiteid . "'
        ";

        $values = $connection->fetchAll($query);

        $header = [];
        $header[] = [
            'Order number' => 'Order Number',
            'Invoice Number' => 'Invoice Number',
            'Total Tax' => 'Total Tax',
            'Sales Tax' => 'Sales Tax',
            'Excise Tax' => 'Excise Tax',
            'Shipping Tax' => 'Shipping Tax',
        ];

        $report = [];
        $report = array_merge($header, $values);

        $fileName = 'Tax_Report.csv';
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
