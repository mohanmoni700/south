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

    public function __construct(AbstractItem $item)
    {
        $this->item = $item;
    }

    public function afterIsAllowed(CartInterface $subject, $result)
    {
        if ($this->item->getOptionByCode('amasty_quote_price')) {
            return false;
        }
        return $result;
    }
}
