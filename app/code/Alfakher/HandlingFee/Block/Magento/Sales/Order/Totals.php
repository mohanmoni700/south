<?php

namespace Alfakher\HandlingFee\Block\Magento\Sales\Order;

/**
 * @author af_bv_op
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @inheritdoc
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];
        $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
            ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        );

        /**
         * Add discount
         */
        if ((double) $this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $source->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    'field' => 'discount_amount',
                    'value' => $source->getDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((double) $source->getShippingAmount() || $source->getShippingDescription())) {
            $label = __('Shipping & Handling');
            if ($this->getSource()->getCouponCode() && !isset($this->_totals['discount'])) {
                $label = __('Shipping & Handling (%1)', $this->getSource()->getCouponCode());
            }

            /*af_bv_op; Start*/
            /*
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
            [
            'code' => 'shipping',
            'field' => 'shipping_amount',
            'value' => $this->getSource()->getShippingAmount(),
            'label' => $label,
            ]
            );
             */
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'field' => 'shipping_amount',
                    'value' => $this->getSource()->getShippingAmount() + $this->getSource()->getHandlingFee(),
                    'label' => $label,
                ]
            );
            /*af_bv_op; End*/
        } elseif ($this->getSource()->getHandlingFee() > 0) {
            /*af_bv_op; Start*/
            $label = __('Shipping & Handling');
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'field' => 'shipping_amount',
                    'value' => $this->getSource()->getHandlingFee(),
                    'label' => $label,
                ]
            );
            /*af_bv_op; End*/
        }

        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        /**
         * Base grandtotal
         */
        if ($this->getOrder()->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'base_grandtotal',
                    'value' => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
                    'label' => __('Grand Total to be Charged'),
                    'is_formated' => true,
                ]
            );
        }
        return $this;
    }
}
