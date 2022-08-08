<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SalesGraphQl
 * @author    CORRA
 */

namespace HookahShisha\SalesGraphQl\Model\Resolver\Invoice;

use Closure;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;
use Magento\SalesGraphQl\Model\Resolver\Invoice\InvoiceItems as SourceInvoiceItems;

class InvoiceItems extends SourceInvoiceItems
{
    /**
     * @var OrderItemProvider
     */
    private OrderItemProvider $orderItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param OrderItemProvider $orderItemProvider
     */
    public function __construct(
        ValueFactory $valueFactory,
        OrderItemProvider $orderItemProvider
    ) {
        parent::__construct($valueFactory, $orderItemProvider);

        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * Get invoice items data as promise
     *
     * @param OrderInterface $order
     * @param array $invoiceItems
     * @return Closure
     */
    public function getInvoiceItems(OrderInterface $order, array $invoiceItems): Closure
    {
        $itemsList = [];
        foreach ($invoiceItems as $Item) {
            $this->orderItemProvider->addOrderItemId((int)$Item->getOrderItemId());
        }

        return function () use ($order, $invoiceItems, $itemsList): array {
            foreach ($invoiceItems as $invoiceItem) {
                $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());
                /** @var OrderItemInterface $orderItemModel */
                $orderItemModel = $orderItem['model'];

                // Return only order items which are not part of Alfa Bundle
                if (!$orderItemModel->getParentItem() && !$orderItemModel->getParentAlfaBundle()) {
                    $invoiceItemData = $this->getInvoiceItemData($order, $invoiceItem);
                    if (!empty($invoiceItemData)) {
                        $itemsList[$invoiceItem->getOrderItemId()] = $invoiceItemData;
                    }
                }
            }

            return $itemsList;
        };
    }

    /**
     * Get formatted invoice item data
     *
     * @param OrderInterface $order
     * @param InvoiceItemInterface $invoiceItem
     * @return array
     */
    private function getInvoiceItemData(OrderInterface $order, InvoiceItemInterface $invoiceItem): array
    {
        $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());

        return [
            'id' => base64_encode($invoiceItem->getEntityId()),
            'product_name' => $invoiceItem->getName(),
            'product_sku' => $invoiceItem->getSku(),
            'product_sale_price' => [
                'value' => $invoiceItem->getPrice(),
                'currency' => $order->getOrderCurrencyCode()
            ],
            'quantity_invoiced' => $invoiceItem->getQty(),
            'model' => $invoiceItem,
            'product_type' => $orderItem['product_type'],
            'order_item' => $orderItem,
            'discounts' => $this->formatDiscountDetails($order, $invoiceItem)
        ];
    }

    /**
     * Returns formatted information about an applied discount
     *
     * @param OrderInterface $associatedOrder
     * @param InvoiceItemInterface $invoiceItem
     * @return array
     */
    private function formatDiscountDetails(OrderInterface $associatedOrder, InvoiceItemInterface $invoiceItem) : array
    {
        if ($associatedOrder->getDiscountDescription() === null
            && $invoiceItem->getDiscountAmount() == 0
            && $associatedOrder->getDiscountAmount() == 0
        ) {
            $discounts = [];
        } else {
            $discounts[] = [
                'label' => $associatedOrder->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs($invoiceItem->getDiscountAmount()) ?? 0,
                    'currency' => $associatedOrder->getOrderCurrencyCode()
                ]
            ];
        }

        return $discounts;
    }
}
