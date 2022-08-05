<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Report;

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
        so.increment_id AS OrderId,
        so.created_at AS OrderDate,
        si.increment_id AS InvoiceId,
        si.created_at AS InvoiceDate,
        com.company_name AS CompanyName,
        so.customer_id AS CustomerId,
        so.customer_firstname AS CustomerFirstName,
        so.customer_lastname AS CustomerLastName,
        soa.street AS StreetAddress,
        soa.city AS City,
        soa.region AS State,
        soa.postcode AS Zipcode,
        soa.country_id AS Country,
        so.subtotal AS SubTotal,
        so.base_shipping_amount AS ShippingCharge,
        so.base_shipping_tax_amount AS ShippingTax,
        so.excise_tax AS ExciseTax,
        so.sales_tax AS SalesTax,
        so.base_discount_amount AS Discount,
        so.gross_margin AS GrossMargin,
        sp.cc_trans_id AS CCTransactionId,
        sp.last_trans_id AS LastTransactionId,
        so.grand_total AS GrandTotal,
        so.purchase_order AS PurchaseOrder
    FROM
        sales_order AS so
    INNER JOIN
        sales_invoice AS si
            ON si.order_id = so.entity_id
    INNER JOIN
        sales_order_address AS soa
            ON soa.parent_id = so.entity_id
    LEFT JOIN
        sales_order_payment AS sp
            ON sp.parent_id = so.entity_id
    LEFT JOIN
        company AS com
            ON com.super_user_id = so.customer_id
    WHERE
        so.state = 'complete'
        AND so.created_at >=  '" . $startdate . "'
        AND so.created_at <= '" . $enddate . "'
        AND so.store_id = '" . $websiteid . "'
        AND si.increment_id IS NOT NULL
        AND soa.address_type = 'shipping'
    GROUP BY
        so.increment_id,
        so.created_at,
        si.increment_id,
        si.created_at,
        so.customer_id,
        so.base_discount_amount,
        so.base_shipping_amount,
        so.excise_tax,
        so.sales_tax,
        so.grand_total,
        so.customer_firstname,
        so.customer_lastname,
        soa.city,
        soa.region,
        soa.country_id,
        so.subtotal,
        so.base_shipping_tax_amount,
        so.gross_margin,
        sp.method,
        sp.cc_trans_id,
        sp.last_trans_id,
        soa.street,
        soa.postcode,
        so.purchase_order,
        com.super_user_id,
        com.company_name
    ORDER BY
        so.created_at";

        $values = $connection->fetchAll($query);

        $header = [];
        $header[] = [
            'Order number' => 'Order number',
            'Order date' => 'Order date',
            'Invoice number' => 'Invoice number',
            'Invoice date' => 'Invoice date',
            'Company Name' => 'Company Name',
            'Customer ID' => 'Customer ID',
            'Customer FirstName' => 'Customer FirstName',
            'Customer LastName' => 'Customer LastName',
            'Shipping address Street' => 'Shipping address Street',
            'Shipping address City' => 'Shipping address City',
            'Shipping address State' => 'Shipping address State',
            'Shipping address Zipcode' => 'Shipping address Zipcode',
            'Shipping address Country' => 'Shipping address Country',
            'Sub Total' => 'Sub Total',
            'Shipping charge' => 'Shipping charge',
            'Shipping tax' => 'Shipping tax',
            'Tobacco tax charge' => 'Tobacco tax charge',
            'Sales tax charge' => 'Sales tax charge',
            'Discounts' => 'Discounts',
            'Gross margin' => 'Gross margin',
            'CCTransactionId' => 'CCTransactionId',
            'LastTransactionId' => 'LastTransactionId',
            'Grand Total' => 'Grand Total',
            'Purchase Order' => 'Purchase Order',

        ];

        $report = [];
        $report = array_merge($header, $values);

        $fileName = 'order_excise_report.csv';
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
