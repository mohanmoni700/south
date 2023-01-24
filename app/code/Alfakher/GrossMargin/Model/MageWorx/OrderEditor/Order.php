<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Model\MageWorx\OrderEditor;

/**
 * @author af_bv_op
 */

use MageWorx\OrderEditor\Api\ChangeLoggerInterface;
use MageWorx\OrderEditor\Model\Order\Item as OrderEditorOrderItem;

class Order extends \MageWorx\OrderEditor\Model\Order
{

    /**
     * Collect Order Totals
     */
    public function collectOrderTotals()
    {
        $totalQtyOrdered = 0;
        $weight = 0;
        $totalItemCount = 0;
        $baseDiscountTaxCompensationAmount = 0;
        $baseDiscountAmount = 0;
        $baseTotalWeeeDiscount = 0;
        $baseSubtotal = 0;
        $baseSubtotalInclTax = 0;

        /*af_bv_op; Start*/
        $grandCost = 0;
        /*af_bv_op; End*/

        /** @var OrderEditorOrderItem $orderItem */
        foreach ($this->getItems() as $orderItem) {
            $baseDiscountAmount += $orderItem->getBaseDiscountAmount();

            //bundle part
            if ($orderItem->getParentItem()) {
                continue;
            }

            $baseDiscountTaxCompensationAmount += $orderItem->getBaseDiscountTaxCompensationAmount();

            $totalQtyOrdered += $orderItem->getQtyOrdered();
            $totalItemCount++;
            $weight += $orderItem->getRowWeight();
            $baseSubtotal += $orderItem->getBaseRowTotal(); /* RowTotal for item is a subtotal */
            $baseSubtotalInclTax += $orderItem->getBaseRowTotalInclTax();
            $baseTotalWeeeDiscount += $orderItem->getBaseDiscountAppliedForWeeeTax();

            /*af_bv_op; Start*/
            $grandCost += $orderItem->getQtyOrdered() * $orderItem->getProduct()->getCost();
            /*af_bv_op; End*/
        }

        /* convert currency */
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $orderCurrencyCode = $this->getOrderCurrencyCode();

        if ($baseCurrencyCode === $orderCurrencyCode) {
            $discountAmount = $baseDiscountAmount + $this->getBaseShippingDiscountAmount();
            $discountTaxCompensationAmount = $baseDiscountTaxCompensationAmount;
            $subtotal = $baseSubtotal;
            $subtotalInclTax = $baseSubtotalInclTax;
        } else {
            $discountAmount = $this->getBaseCurrency()
                ->convert(
                    $baseDiscountAmount + $this->getBaseShippingDiscountAmount(),
                    $orderCurrencyCode
                );
            $discountTaxCompensationAmount = $this->getBaseCurrency()
                ->convert(
                    $baseDiscountTaxCompensationAmount,
                    $orderCurrencyCode
                );
            $subtotal = $this->getBaseCurrency()
                ->convert(
                    $baseSubtotal,
                    $orderCurrencyCode
                );
            $subtotalInclTax = $this->getBaseCurrency()
                ->convert(
                    $baseSubtotalInclTax,
                    $orderCurrencyCode
                );
        }

        if ($this->getWeight() != $weight) {
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::SIMPLE_MESSAGE_KEY => __(
                        'Total Weight has been changed from <b>%1</b> to <b>%2</b>',
                        round($this->getWeight(), 4),
                        round($weight, 4)
                    ),
                ]
            );
        }

        $this->setTotalQtyOrdered($totalQtyOrdered)
            ->setWeight($weight)
            ->setSubtotal($subtotal)
            ->setBaseSubtotal($baseSubtotal)
            ->setSubtotalInclTax($subtotalInclTax)
            ->setBaseSubtotalInclTax($baseSubtotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensationAmount)
            ->setDiscountAmount('-' . $discountAmount)
            ->setBaseDiscountAmount('-' . $baseDiscountAmount)
            ->setTotalItemCount($totalItemCount);

        /*af_bv_op; Start*/
        $this->setGrossMargin(($subtotal - $grandCost) / $subtotal * 100);
        /*af_bv_op; End*/

        $this->calculateGrandTotal();

        $this->orderRepository->save($this);
    }
}
