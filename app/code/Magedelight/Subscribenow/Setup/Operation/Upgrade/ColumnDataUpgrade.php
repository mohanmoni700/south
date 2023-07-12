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

use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Setup\Operation\Create\ProductSubscribers;
use Magento\Shipping\Model\Config\Source\Allmethods;
use Psr\Log\LoggerInterface;

/**
 * Class ColumnDataUpgrade
 *
 * Upgrading columns data from `addtional_data` json to single column
 *
 * @since 200.0.2
 * @package \Magedelight\Subscribenow\Setup\Operation\Upgrade
 */
class ColumnDataUpgrade
{
    private $productSubscriberFactory;
    private $shippingMethods;
    private $logger;
    private $shippingMethodArray = [];
    private $paymentMethodArray = [];

    public function __construct(
        ProductSubscribersFactory $productSubscriberFactory,
        Allmethods $shipping,
        LoggerInterface $logger
    ) {
        $this->productSubscriberFactory = $productSubscriberFactory;
        $this->shippingMethods = $shipping;
        $this->logger = $logger;
    }

    /**
     * @param $setup
     * @param $sales
     * @return bool
     */
    public function upgradeData($setup, $sales)
    {
        $table = $setup->getTable(ProductSubscribers::TBL);
        $salesTable = $setup->getTable('sales_order');
        $itemTable = $setup->getTable('sales_order_item');

        $wh = 'product_name IS NULL';
        $columns = ['subscription_id', 'profile_id', 'payment_method_code',
            'initial_order_id', 'shipping_method_code', 'additional_info'];
        $select = $sales->select()->from($table . ' AS main_table', $columns);
        $select->join(
            ['sales' => $salesTable],
            'sales.increment_id = main_table.initial_order_id',
            ['entity_id', 'increment_id']
        );
        $select->join(
            ['sales_items' => $itemTable],
            'sales_items.order_id = sales.entity_id AND sales_items.product_id = main_table.product_id',
            ['sku', 'name']
        );
        $select->where($wh);

        $query = $sales->query($select);

        if ($query->rowCount($select)) {
            while ($row = $query->fetch()) {
                try {
                    $this->processCollection($sales, $table, $row);
                } catch (\Exception $ex) {
                    $this->logger->info("#" . $row['profile_id'] . ": " . $ex->getMessage());
                }
            }
        }

        return true;
    }

    private function processCollection($sales, $table, $row)
    {
        $paymentMethodCode = $row['payment_method_code'];
        $initialOrderId = $row['initial_order_id'];
        $shippingMethodCode = $row['shipping_method_code'];
        $additionalInfo = $row['additional_info'];
        if ($additionalInfo) {
            $additionalInfo = json_decode($row['additional_info'], true);
        }

        $paymentTitle = $this->getPaymentTitle($paymentMethodCode, $initialOrderId);
        $shippingTitle = $this->getShippingTitle($shippingMethodCode);

        if ($paymentTitle && $shippingTitle) {
            $additionalInfo['product_sku'] = $row['sku'];
            $additionalInfo['shipping_title'] = trim($shippingTitle);
            $additionalInfoJson = json_encode($additionalInfo);

            $sales->update(
                $table,
                [
                    'product_name' => $row['name'],
                    'payment_title' => $paymentTitle,
                    'additional_info' => $additionalInfoJson
                ],
                sprintf("subscription_id = '%s'", $row['subscription_id'])
            );
        }
    }

    public function getPaymentTitle($code = null, $orderId = null)
    {
        $title = null;
        if ($code && isset($this->paymentMethodArray[$code])) {
            $title = $this->paymentMethodArray[$code];
        }

        if ($orderId) {
            $order = $this->productSubscriberFactory->create()->getOrderByIncrementId($orderId);
            if ($order && $order->getId() && $order->getPayment()) {
                $method = $order->getPayment()->getMethod();
                if (!isset($this->paymentMethodArray[$method])) {
                    $title = $order->getPayment()->getAdditionalInformation('method_title');
                    $this->paymentMethodArray[$method] = $title;
                }
            }
        }
        return $title;
    }

    public function getShippingTitle($code)
    {
        if (!$this->shippingMethodArray) {
            $shippingMethods = $this->shippingMethods->toOptionArray();
            $methods = [];
            foreach ($shippingMethods as $method) {
                if (!empty($method['value']) && is_array($method['value'])) {
                    foreach ($method['value'] as $key => $value) {
                        $subtitle = $this->deleteAllBetweenStr('[', ']', $value['label']);
                        $title = $subtitle ? $method['label'] . " - " . $subtitle : $method['label'];
                        $methods[$value['value']] = $title;
                    }
                }
            }

            $this->shippingMethodArray = $methods;
        }

        return isset($this->shippingMethodArray[$code]) ? $this->shippingMethodArray[$code] : null;
    }

    private function deleteAllBetweenStr($beginning, $end, $string)
    {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        // recursion to ensure all occurrences are replaced
        return trim(
            $this->deleteAllBetweenStr(
                $beginning,
                $end,
                str_replace($textToDelete, '', $string)
            )
        );
    }
}
