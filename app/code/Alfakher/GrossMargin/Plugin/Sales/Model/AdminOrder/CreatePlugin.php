<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

/**
 * @author af_bv_op
 */
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;

class CreatePlugin
{
    /**
     * Around Import Post Data
     *
     * @param Create $subject
     * @param callable $proceed
     * @param array $data
     * @return mixed
     */
    public function aroundImportPostData(Create $subject, callable $proceed, $data)
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
     * @param Create $subject
     * @param callable $proceed
     * @param Order $order
     * @return mixed
     */
    public function aroundInitFromOrder(Create $subject, callable $proceed, Order $order)
    {
        $result = $proceed($order);

        if ($order->getPurchaseOrder()) {
            $result->getQuote()->setPurchaseOrder($order->getPurchaseOrder())->save();
        }
        return $result;
    }
}
