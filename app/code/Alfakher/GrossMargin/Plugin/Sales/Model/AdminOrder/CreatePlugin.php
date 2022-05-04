<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

/**
 * @author af_bv_op
 */
class CreatePlugin
{
    /**
     * Around Import Post Data
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param callable $proceed
     * @param array $data
     */
    public function aroundImportPostData(\Magento\Sales\Model\AdminOrder\Create $subject, callable $proceed, $data)
    {
        $result = $proceed($data);

        if (isset($data['purchase_order'])) {
            $result->getQuote()->addData(['purchase_order' => $data['purchase_order']]);
        }
        return $result;
    }

    /**
     * Around Init From Order
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order $order
     */
    public function aroundInitFromOrder(\Magento\Sales\Model\AdminOrder\Create $subject, callable $proceed, \Magento\Sales\Model\Order $order)
    {
        $result = $proceed($order);

        if ($order->getPurchaseOrder()) {
            $result->getQuote()->setPurchaseOrder($order->getPurchaseOrder())->save();
        }
        return $result;
    }
}
