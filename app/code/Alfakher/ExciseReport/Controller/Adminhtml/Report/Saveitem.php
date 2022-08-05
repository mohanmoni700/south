<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Report;

/**
 * @ItemExciseReport
 */

use Alfakher\ExciseReport\Block\Adminhtml\Report\ExciseTax;

class Saveitem extends \Magento\Backend\App\Action
{
    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param ExciseTax $block
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        ExciseTax $block
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->block = $block;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $startdate = $post['startdate'] . " 00:00:00";
        $enddate = $post['enddate'] . " 23:59:59";
        $storeid = $post['website'];
        $store_code = $this->block->getWebsiteCode($storeid);
        $connection = $this->resourceConnection->getConnection();
        $header = [];
        $values = [];

        if ($store_code == 'hookah_wholesalers') {
            /*query for b2b item report*/
            $query = "SELECT
			  si.increment_id as Invoice_Id,
			  si.created_at as Invoice_date,
			  so.increment_id as Order_Id,
			  so.created_at as Order_Date,
			  soi.item_id AS Itemid,
			  soi.sku as SKU,
			  soi.weight as Item_Weight,
			  soi.qty_ordered as Ordered_Quantity,
			  soi.qty_invoiced as Invoiced_Quantity,
			  soi.qty_shipped as Shipped_Quantity,
			  soi.excise_tax as Excise_Tax,
			  soi.sales_tax as Sales_Tax,
			  soi.base_cost as Product_Cost,
			  soi.base_price as Price
			FROM
			  sales_order as so
			  left join sales_invoice as si on si.order_id = so.entity_id
			  left join sales_order_item as soi on soi.order_id = so.entity_id
			WHERE
			  so.state = 'complete'
			  AND so.created_at >= '" . $startdate . "'
			  AND so.created_at <= '" . $enddate . "'
			  AND so.store_id = '" . $storeid . "'
			  AND si.increment_id IS NOT Null
			ORDER BY
	 		 so.created_at";

            $fileName = 'b2b_item_excise_report.csv';

        }
        if ($store_code == 'base') {
            /*query for b2c item report*/
            $query = "SELECT
				si.increment_id AS Invoice_Id,
			    si.created_at AS Invoice_date,
			    so.increment_id AS Order_Id,
			    so.created_at AS Order_Date,
			    soi.item_id AS Itemid,
			    soi.sku AS SKU,
			    soi.weight AS Item_Weight,
			    soi.qty_ordered AS Ordered_Quantity,
			    soi.qty_invoiced AS Invoiced_Quantity,
			    soi.qty_shipped AS Shipped_Quantity,
			   	soi.excise_tax AS Excise_Tax,
			    soi.sales_tax AS Sales_Tax,
                    (SELECT value FROM catalog_product_entity_decimal
                        WHERE store_id=0
                        AND row_id=cpe.entity_id
                        AND attribute_id=(SELECT attribute_id FROM eav_attribute
                            WHERE attribute_code = 'cost'
                            )
                    ) AS cost,
			    soi.base_price AS Price
				FROM
				    sales_order AS so
				LEFT JOIN sales_invoice AS si
					ON si.order_id = so.entity_id
				LEFT JOIN sales_order_item AS soi
					ON soi.order_id = so.entity_id
				LEFT JOIN catalog_product_entity AS cpe
					ON soi.sku = cpe.sku
				WHERE
				    so.state = 'complete'
				    AND so.created_at >= '" . $startdate . "'
			  		AND so.created_at <= '" . $enddate . "'
			 		AND so.store_id = '" . $storeid . "'
				    AND si.increment_id IS NOT NULL
				    AND soi.qty_shipped != 0
				ORDER BY
				    so.created_at";

            $fileName = "b2c_item_excise_report.csv";

        }
        $header[] = [
            'Invoice number' => 'Invoice number',
            'Invoice date' => 'Invoice date',
            'Order number' => 'Order number',
            'Order date' => 'Order date',
            'SKU number' => 'SKU number',
            'SKU Name' => 'SKU Name',
            'Weight' => 'Weight',
            'Ordered Quantity' => 'Ordered Quantity',
            'Invoiced Quantity' => 'Invoiced Quantity',
            'Shipped Quantity' => 'Shipped Quantity',
            'Tobacco tax charge' => 'Tobacco tax charge',
            'Sales tax charge' => 'Sales tax charge',
            'SKU Cost' => 'SKU Cost',
            'SKU Price' => 'SKU Price',

        ];
        if (!empty($query)) {
            $values = $connection->fetchAll($query);
        }

        if ($store_code == 'hookah_company' || $store_code == 'hookah') {

            $fileName = "item_excise_report.csv";
        }

        $item_report = array_merge($header, $values);

        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;

        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $item_report
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
