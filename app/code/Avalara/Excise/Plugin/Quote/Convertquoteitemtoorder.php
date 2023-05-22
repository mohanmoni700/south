<?php
namespace Avalara\Excise\Plugin\Quote;

class Convertquoteitemtoorder
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);
        $orderItem->setExciseTax($item->getExciseTax());
        $orderItem->setSalesTax($item->getSalesTax());
        return $orderItem;
    }
}
