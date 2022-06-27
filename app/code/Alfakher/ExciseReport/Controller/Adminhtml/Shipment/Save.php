<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Shipment;

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
        ss.increment_id AS ShipNumber,
        ss.created_at AS ShipDate,
        so.increment_id AS OrderNumber,
        so.created_at AS OrderDate,
        ss.total_qty AS ShipQty

        FROM
        sales_shipment AS ss

        LEFT JOIN
        sales_order AS so
        ON so.entity_id = ss.order_id

        WHERE
        ss.created_at >=  '" . $startdate . "'
        AND ss.created_at <= '" . $enddate . "'
        AND ss.store_id = '" . $websiteid . "'
        ORDER BY
        ss.created_at";

        $values = $connection->fetchAll($query);

        $header = [];
        $header[] = [
            'Ship number' => 'Ship number',
            'Ship date' => 'Ship Date',
            'Order Number' => 'Order Number',
            'Order Date' => 'Order Date',
            'Ship Qty' => 'Ship Qty',

        ];

        $report = [];
        $report = array_merge($header, $values);

        $fileName = 'Shipment_Report.csv';
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
