<?php
declare(strict_types=1);

namespace Alfakher\ShippingEdit\Plugin\Model;

/**
 * Class Set Discount
 */
class Shipping
{

    /**
     * Order set value
     *
     * @param \MageWorx\OrderEditor\Model\Shipping $object
     * @return void
     */
    public function beforeUpdateShippingMethod(\MageWorx\OrderEditor\Model\Shipping $object)
    {
        $order = $object->getOrder();

        $order->collectOrderTotals();

        $order->setOriginalSubtotal(0);
        $order->setOriginalSubtotalInclTax(0);
        $order->setOriginalBaseSubtotal(0);
        $order->setOriginalBaseSubtotalInclTax(0);
        $order->setTotalSubtotalDiscount(0);

        if ($order->getTotalShippingFeeDiscount() > 0) {
            $order->setShippingAmount($order->getOriginalShippingFee());
            $order->setBaseShippingAmount($order->getOriginalBaseShippingAmount());
            $order->setShippingInclTax($order->getOriginalShippingInclTax());
            $order->setBaseShippingInclTax($order->getOriginalBaseShippingInclTax());
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getTotalShippingFeeDiscount());
            $order->setGrandTotal($order->getGrandTotal() + $order->getTotalShippingFeeDiscount());
        } elseif ($order->getOriginalShippingFee() > 0) {
            $order->setShippingAmount($order->getOriginalShippingFee());
            $order->setBaseShippingAmount($order->getOriginalBaseShippingAmount());
            $order->setShippingInclTax($order->getOriginalShippingInclTax());
            $order->setBaseShippingInclTax($order->getOriginalBaseShippingInclTax());
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getOriginalShippingFee());
            $order->setGrandTotal($order->getGrandTotal() + $order->getOriginalShippingFee());
        }

        $order->setOriginalShippingFee(0);
        $order->setOriginalBaseShippingAmount(0);
        $order->setOriginalShippingInclTax(0);
        $order->setOriginalBaseShippingInclTax(0);
        $order->setTotalShippingFeeDiscount(0);
        $order->setHandlingFee(0);
    }
}
