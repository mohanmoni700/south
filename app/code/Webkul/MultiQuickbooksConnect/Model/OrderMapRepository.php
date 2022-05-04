<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MultiQuickbooksConnect
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model;

class OrderMapRepository implements \Webkul\MultiQuickbooksConnect\Api\OrderMapRepositoryInterface
{
    /**
     * @var OrderMapFactory
     */
    private $quickbooksOrder;

    /**
     * @param OrderMapFactory $quickbooksOrder
     */
    public function __construct(
        OrderMapFactory $quickbooksOrder
    ) {
        $this->quickbooksOrder = $quickbooksOrder;
    }

    /**
     * @param  int $id
     * @return object
     */
    public function getById($id)
    {
        $order = $this->quickbooksOrder->create()->load($id);
        return $quickbooksOrder;
    }

    /**
     * @param  int $mageOrderId
     * @return object
     */
    public function getByMageOrderId($mageOrderId)
    {
        $order = $this->quickbooksOrder->create()->getCollection()
                            ->addFieldToFilter('mage_order_id', ['eq' => $mageOrderId])
                            ->setPageSize(1)->getFirstItem();
        return $order;
    }

    /**
     * get collection by entity ids
     * @param  array $entityIds
     * @return object
     */
    public function getCollectionByIds(array $entityIds)
    {
        $orderCollection = $this->quickbooksOrder->create()->getCollection()
                            ->addFieldToFilter(
                                'entity_id',
                                [
                                    'in' => $entityIds
                                ]
                            );
        return $orderCollection;
    }

    /**
     * Get mapped order collection by quickbooks account id
     *
     * @param string $accountId
     * @return void
     */
    public function getCollectionByAccountId($accountId, $joinFlag = false)
    {
        $orderCollection = $this->quickbooksOrder->create()->getCollection()
                            ->addFieldToFilter(
                                'account_id',
                                [
                                    'eq' => $accountId
                                ]
                            );
        if ($joinFlag) {
            $orderCollection->getSelect()->joinLeft(
                ['secondTable' => $orderCollection->getTable('sales_order_grid')],
                'main_table.mage_order_id = secondTable.entity_id',
                [
                    'entity_id' => 'main_table.entity_id',
                    'store_id' => 'secondTable.store_id',
                    'customer_email' => 'secondTable.customer_email',
                    'status' => 'secondTable.status',
                    'increment_id' => 'secondTable.increment_id',
                    'mage_invoice_id' => 'main_table.mage_invoice_id',
                    'created_at' => 'secondTable.created_at',
                    'mapped_on' => 'main_table.created_at'
                ]
            );
        }
        return $orderCollection;
    }
}
