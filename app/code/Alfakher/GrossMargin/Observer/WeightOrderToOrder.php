<?php

namespace Alfakher\GrossMargin\Observer;

/**
 * @author af_bv_op
 */
use Magento\Framework\Event\Observer;

class WeightOrderToOrder implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $totalWeight = 0;
        try {
            $items = $order->getAllItems();
            foreach ($items as $item) {
                $totalWeight += $item->getProduct()->getWeight() * $item->getQtyOrdered();
            }
            $order->setTotalOrderWeight($totalWeight)->save();
        } catch (\Exception $e) {}
    }
}
