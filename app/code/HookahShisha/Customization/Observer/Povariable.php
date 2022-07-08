<?php

namespace HookahShisha\Customization\Observer;

use Magento\Framework\Event\Observer;

class Povariable implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute
     *
     * @param mixed $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getSource();
        if (!$order instanceof \Magento\Sales\Model\Order) {
            $order = $order->getOrder();
        }
        $Purchase = $order->getPurchaseOrder();
        $observer->getVariableList()->setData('purchase_order', $Purchase);
    }
}
