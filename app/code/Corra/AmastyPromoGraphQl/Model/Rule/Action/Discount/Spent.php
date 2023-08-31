<?php

namespace Corra\AmastyPromoGraphQl\Model\Rule\Action\Discount;

use Amasty\Promo\Model\Rule\Action\Discount\Spent as DiscountSpent;

/**
 * Class Spent for inclusing Tax amount
 * Corra\AmastyPromoGraphQl\Model\Rule\Action\Discount
 */
class Spent extends DiscountSpent
{
    /**
     * @param \Magento\Quote\Model\Quote\Address\Item[] $ruleItems
     * @return float|int
     */
    protected function getItemsSpent($ruleItems)
    {
        $total = 0;
        $withDiscount = $this->config->isDiscountIncluded();
        foreach ($ruleItems as $item) {
            $total += $item->getBaseRowTotal() + $item->getBaseTaxAmount();
            if ($withDiscount) {
                $total -= $item->getBaseDiscountAmount();
            }
        }

        return $total;
    }
}
