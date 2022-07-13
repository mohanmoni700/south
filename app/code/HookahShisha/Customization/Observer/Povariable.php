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

        if ($order->getExciseTax() > 0) {
            $excise_tax = "All State Tobacco Product taxes are included in the total amount of this invoice";
        } else {
            $excise_tax = "Purchaser Responsible for excise tax";
        }
        $observer->getVariableList()->setData('excise_tax', $excise_tax);
    }
}
