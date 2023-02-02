<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Observer;

/**
 * af_bv_op
 */
use Magento\Framework\Event\Observer;

class PurchaseOrderToOrder implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        $order->setPurchaseOrder($quote->getPurchaseOrder())->save();
    }
}
