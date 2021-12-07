<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Model\Plugin\Quote;

use Closure;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;

/**
 * Plugin to set alfa_bundle attribute value from quote item alfa_bundle attribute value
 */
class AlfaBundleToOrderItem
{
    /**
     * Set alfa_bundle attribute value from quote item alfa_bundle attribute value
     *
     * @param ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return Item
     */
    public function aroundConvert(
        ToOrderItem $subject,
        Closure $proceed,
        AbstractItem $item,
        array $additional = []
    ): Item {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setAlfaBundle($item->getAlfaBundle());
        $orderItem->setInAlfaBundle($item->getInAlfaBundle());

        return $orderItem;
    }
}
