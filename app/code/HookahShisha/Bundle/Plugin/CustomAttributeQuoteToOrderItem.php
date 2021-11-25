<?php
namespace HookahShisha\Bundle\Plugin;

use Magento\Sales\Api\Data\OrderItemInterface;

class CustomAttributeQuoteToOrderItem
{
    /**
     * Alpha is bunle attribute set on ordet item
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return OrderItemInterface
     */

    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setAlfaIsBundle($item->getAlfaIsBundle());
        return $orderItem;
    }
}
