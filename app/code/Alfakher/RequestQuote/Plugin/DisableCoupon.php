<?php

namespace Alfakher\RequestQuote\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class DisableCoupon
{
    /**
     * @var AbstractItem
     */
    private AbstractItem $item;

    /**
     * @param AbstractItem $item
     */
    public function __construct(AbstractItem $item)
    {
        $this->item = $item;
    }

    /**
     * After plugin to disable coupon if cart contain quotes products
     *
     * @param CartInterface $subject
     * @param $result
     * @return false|mixed
     */
    public function afterIsAllowed(CartInterface $subject, $result)
    {
        if ($this->item->getOptionByCode('amasty_quote_price')) {
            return false;
        }
        return $result;
    }
}
