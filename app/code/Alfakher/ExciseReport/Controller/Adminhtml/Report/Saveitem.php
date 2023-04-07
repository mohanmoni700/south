<?php

namespace Alfakher\ExciseReport\Controller\Adminhtml\Report;

/**
 * @ItemExciseReport
 */

use Alfakher\ExciseReport\Block\Adminhtml\Report\ExciseTax;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;

class Saveitem extends \Magento\Backend\App\Action
{
    public const B2B_ITEM_REPORT = "b2b_item_excise_report.csv";
    public const B2C_ITEM_REPORT = "b2c_item_excise_report.csv";
    public const WITHOUT_ITEM_REPORT = "without_cost_excise_report.csv";

    /**
     * Construct
     *
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param FileFactory $fileFactory
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param ExciseTax $block
     */

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
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
        $fileName = "item_excise_report.csv";
        $check_value = isset($post['check']) ? 1 : 0;
        $startdate = $post['startdate'] . " 00:00:00";
        $enddate = $post['enddate'] . " 23:59:59";
        $storeid = $post['website'];
        $store_code = $this->block->getWebsiteCode($storeid);
        $connection = $this->resourceConnection->getConnection();
        $header = [];
        $values = [];

        if ($store_code === 'base' && $check_value === 1) {
            /*query for b2c without cost and superpack report*/
            $query = $this->withoutCostb2c($startdate, $enddate, $storeid);
            $fileName = self::WITHOUT_ITEM_REPORT;
        }
        if ($store_code === 'hookah_wholesalers' && $check_value === 1) {
            /*query for b2b without cost and superpack report*/
            $query = $this->withoutCostb2b($startdate, $enddate, $storeid);
            $fileName = self::WITHOUT_ITEM_REPORT;
        }
        if ($store_code === 'hookah_wholesalers' && $check_value === 0) {
            /*query for b2b item report*/
            $query = $this->b2bItems($startdate, $enddate, $storeid);
            $fileName = self::B2B_ITEM_REPORT;
        }
        if ($store_code === 'base' && $check_value === 0) {
            /*query for b2c item report*/
            $query = $this->b2cItems($startdate, $enddate, $storeid);
            $fileName = self::B2C_ITEM_REPORT;
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

        if ($store_code === 'hookah_wholesalers') {
            $header[0]['TIN_number'] = "TIN_number";
            $header[0]['Sales_Tax_No'] = "Sales_Tax_No";
            $header[0]['Expiry_date_of_Sales_Tax_No'] = "Expiry_date_of_Sales_Tax_No";
            $header[0]['Tobacco_Tax_No'] = "Tobacco_Tax_No";
            $header[0]['Expiry_date_of_Tobacco_Tax_No'] = "Expiry_date_of_Tobacco_Tax_No";
        }

        if (!empty($query)) {
            $values = $connection->fetchAll($query);
        }

        if ($check_value === 1) {
            unset($header[0]['SKU Cost']);
            /*to remove superpack column from withoutCost() csv*/
            foreach ($values as $key => $val) {
                array_pop($values[$key]);
            }
            /*to remove superpack column END*/
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
    /**
     * B2c Items without cost and superpack
     *
     * @param string $startdate
     * @param string $enddate
     * @param string $storeid
     * @return array
     */
    public function withoutCostb2c($startdate, $enddate, $storeid)
    {
        return "SELECT
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
                IF(
                    soi.base_price = 0,
                    (
                        IF(
                            (SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id) is null,
                            soi.base_price,
                            (
                            SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id)
                          )
                    ),
                    soi.base_price
                  ) as Price,
                (SELECT value FROM catalog_product_entity_int
                    WHERE store_id=0
                    AND row_id=cpe.entity_id
                    AND attribute_id=(SELECT attribute_id FROM eav_attribute
                        WHERE attribute_code = 'is_superpack'
                    )
                ) AS superpack
                FROM
                sales_order as so
                left join sales_invoice as si on si.order_id = so.entity_id
                left join sales_order_item as soi on soi.order_id = so.entity_id
                LEFT JOIN catalog_product_entity AS cpe ON soi.sku = cpe.sku
                WHERE
                 so.state = 'complete'
                AND so.created_at >= '" . $startdate . "'
                AND so.created_at <= '" . $enddate . "'
                AND so.store_id = '" . $storeid . "'
                AND si.increment_id IS NOT Null
                AND soi.product_type != 'configurable'
                AND soi.product_type != 'bundle'
                HAVING
                superpack is null
                or superpack = 0
                ORDER BY
                so.created_at";
    }

    /**
     * B2b Items without cost and superpack
     *
     * @param string $startdate
     * @param string $enddate
     * @param string $storeid
     * @return array
     */
    public function withoutCostb2b($startdate, $enddate, $storeid)
    {
        return "SELECT
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
                IF(
                    soi.base_price = 0,
                    (
                        IF(
                            (SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id) is null,
                            soi.base_price,
                            (
                            SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id)
                          )
                    ),
                    soi.base_price
                  ) as Price,
                co.tin_number as TIN_number,
                co.vat_tax_id as Sales_Tax_No,
                (SELECT mydoc.expiry_date
                    from alfakher_mydocument_mydocument as mydoc
                    where mydoc.document_name = 'Sales Tax/Resale License'
                    AND mydoc.is_customerfrom_usa = '1'
                    AND mydoc.customer_id = so.customer_id
                    order by mydoc.mydocument_id LIMIT 1
                )as Expiry_date_of_Sales_Tax_No,
                co.tobacco_permit_number as Tobacco_Tax_No,
                (SELECT mydoc.expiry_date
                    from alfakher_mydocument_mydocument as mydoc
                    where mydoc.document_name = 'State Tobacco License'
                    AND mydoc.is_customerfrom_usa = '1'
                    AND mydoc.customer_id = so.customer_id
                    order by mydoc.mydocument_id LIMIT 1
                )as Expiry_date_of_Tobacco_Tax_No,
                (SELECT value FROM catalog_product_entity_int
                    WHERE store_id=0
                    AND row_id=cpe.entity_id
                    AND attribute_id=(SELECT attribute_id FROM eav_attribute
                        WHERE attribute_code = 'is_superpack'
                    )
                ) AS superpack
                FROM
                sales_order as so
                left join sales_invoice as si on si.order_id = so.entity_id
                left join sales_order_item as soi on soi.order_id = so.entity_id
                LEFT JOIN catalog_product_entity AS cpe ON soi.sku = cpe.sku
                join company_advanced_customer_entity as cace on so.customer_id = cace.customer_id
                join company as co on co.entity_id = cace.company_id
                WHERE
                 so.state = 'complete'
                AND so.created_at >= '" . $startdate . "'
                AND so.created_at <= '" . $enddate . "'
                AND so.store_id = '" . $storeid . "'
                AND si.increment_id IS NOT Null
                AND soi.product_type != 'configurable'
                AND soi.product_type != 'bundle'
                HAVING
                superpack is null
                or superpack = 0
                ORDER BY
                so.created_at";
    }
    /**
     * B2b Items
     *
     * @param string $startdate
     * @param string $enddate
     * @param string $storeid
     * @return array
     */
    public function b2bItems($startdate, $enddate, $storeid)
    {
        return "SELECT
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
                IF(
                    soi.base_price = 0,
                    (
                        IF(
                            (SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id) is null,
                            soi.base_price,
                            (
                            SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id)
                          )
                    ),
                    soi.base_price
                ) as Price,
                co.tin_number as TIN_number,
                co.vat_tax_id as Sales_Tax_No,
                (SELECT mydoc.expiry_date
                    from alfakher_mydocument_mydocument as mydoc
                    where mydoc.document_name = 'Sales Tax/Resale License'
                    AND mydoc.is_customerfrom_usa = '1'
                    AND mydoc.customer_id = so.customer_id
                    order by mydoc.mydocument_id LIMIT 1
                )as Expiry_date_of_Sales_Tax_No,
                co.tobacco_permit_number as Tobacco_Tax_No,
                (SELECT mydoc.expiry_date
                    from alfakher_mydocument_mydocument as mydoc
                    where mydoc.document_name = 'State Tobacco License'
                    AND mydoc.is_customerfrom_usa = '1'
                    AND mydoc.customer_id = so.customer_id
                    order by mydoc.mydocument_id LIMIT 1
                )as Expiry_date_of_Tobacco_Tax_No
                FROM
                sales_order as so
                left join sales_invoice as si on si.order_id = so.entity_id
                left join sales_order_item as soi on soi.order_id = so.entity_id
                join company_advanced_customer_entity as cace on so.customer_id = cace.customer_id
                join company as co on co.entity_id = cace.company_id
                WHERE
                so.state = 'complete'
                AND so.created_at >= '" . $startdate . "'
                AND so.created_at <= '" . $enddate . "'
                AND so.store_id = '" . $storeid . "'
                AND si.increment_id IS NOT Null
                AND soi.product_type != 'configurable'
                AND soi.product_type != 'bundle'
                ORDER BY
                so.created_at";
    }
    /**
     * B2c Items
     *
     * @param string $startdate
     * @param string $enddate
     * @param string $storeid
     * @return array
     */
    public function b2cItems($startdate, $enddate, $storeid)
    {
        return "SELECT
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
                soi.base_cost AS cost,
                IF(
                    soi.base_price = 0,
                    (
                        IF(
                            (SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id) is null,
                            soi.base_price,
                            (
                            SELECT base_price
                            from sales_order_item
                            where item_id = soi.parent_item_id)
                          )
                    ),
                    soi.base_price
                ) as Price
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
                    AND soi.product_type != 'configurable'
                    AND soi.product_type != 'bundle'
                ORDER BY
                    so.created_at";
    }
}
