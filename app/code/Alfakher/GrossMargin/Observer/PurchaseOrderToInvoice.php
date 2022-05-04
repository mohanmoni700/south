<?php

namespace Alfakher\GrossMargin\Observer;

/**
 * af_bv_op
 */
use Magento\Framework\Event\Observer;

class PurchaseOrderToInvoice implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        try {
            $order = $invoice->getOrder();
            $order->setPurchaseOrder(
                $invoice->getOrder()->getPurchaseOrder()
            )->save();
        } catch (\Exception $e) {

        }
    }
}
