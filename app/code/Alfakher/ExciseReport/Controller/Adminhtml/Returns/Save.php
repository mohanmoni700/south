<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Returns;

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
        mr.increment_id AS RmaNumber,
        mr.date_requested AS RmaDate,
        mr.order_increment_id AS OrderNumber,
        mr.order_date AS OrderDate,
        mr.status AS Status
        FROM
        magento_rma_grid AS mr

        WHERE
        mr.date_requested >=  '" . $startdate . "'
        AND mr.date_requested <= '" . $enddate . "'
        AND mr.store_id = '" . $websiteid . "'
        ORDER BY
        mr.date_requested";

        $values = $connection->fetchAll($query);

        $header = [];
        $header[] = [
            'Rma Number' => 'Rma Number',
            'Rma Date' => 'Rma Date',
            'Order Number' => 'Order Number',
            'Order Date' => 'Order Date',
            'Status' => 'Status',
        ];

        $report = [];
        $report = array_merge($header, $values);

        $fileName = 'Return_Report.csv';
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
