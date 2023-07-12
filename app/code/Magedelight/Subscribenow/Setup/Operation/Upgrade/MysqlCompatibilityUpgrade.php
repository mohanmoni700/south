<?php
/**
 * Magedelight
 * Copyright (C) 2022 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2022 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Setup\Operation\Upgrade;

use Magedelight\Subscribenow\Setup\Operation\Create\ProductSubscribers;
use Magento\Sales\Model\ResourceModel\Order\Item;

/**
 * Class MysqlCompatibillity
 * Subscribenow PRO mysql 5.6 compatibility
 *
 * @since 200.4.0
 * @package \Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class MysqlCompatibilityUpgrade
{
    public function __construct(
        Item\CollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    public function upgradeData($setup, $sales)
    {
        $this->updateSkuQty($setup, $sales);

        $collection = $this->itemCollectionFactory->create();
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $productOptions = $item->getProductOptions();

                $subscriptionOption = $productOptions['info_buyRequest']['options']['_1'] ?? false;
                if ($subscriptionOption == 'subscription') {
                    $item->setIsSubscription(1)->save();
                }
            }
        }
    }

    public function updateSkuQty($setup, $sales)
    {
        $table = $setup->getTable(ProductSubscribers::TBL);
        $wh = 'product_sku IS NULL';
        $columns = ['subscription_id', 'profile_id', 'product_id', 'order_item_info', 'additional_info'];
        $select = $sales->select()->from($table, $columns)->where($wh);
        $query = $sales->query($select);

        if ($query->rowCount($select)) {
            while ($row = $query->fetch()) {
                try {
                    if ($orderItemInfo = $row['order_item_info']) {
                        $orderItemInfo = json_decode($orderItemInfo, true);
                    }

                    if ($additionalInfo = $row['additional_info']) {
                        $additionalInfo = json_decode($additionalInfo, true);
                    }

                    if ($additionalInfo && $orderItemInfo) {
                        $sales->update(
                            $table,
                            [
                                'product_sku' => $additionalInfo['product_sku'] ?? null,
                                'qty_subscribed' => $orderItemInfo['qty'] ?? 1
                            ],
                            sprintf("subscription_id = '%s'", $row['subscription_id'])
                        );
                    }
                } catch (\Exception $ex) {
                    $ex;
                }
            }
        }
    }
}
