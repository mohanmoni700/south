<?php

declare(strict_types=1);

namespace Alfakher\GiftProduct\Pricing\Price;

use Magento\GiftCard\Pricing\Price\FinalPrice;

class MaximalFinalPrice extends FinalPrice
{
    /**
     * @inheritDoc
     */
    public function getAmounts(): array
    {
        if (!empty($this->amountsCache)) {
            return $this->amountsCache;
        }

        if (!empty($this->product->getGiftcardAmounts())) {
            foreach ($this->product->getGiftcardAmounts() as $amount) {
                $this->amountsCache[] = $this->priceCurrency->convertAndRound($amount['website_value']);
            }
        }

        if ($this->product->getAllowOpenAmount()) {
            $this->amountsCache[] = $this->priceCurrency->convertAndRound($this->product->getOpenAmountMax());
        }

        rsort($this->amountsCache);
        return $this->amountsCache;
    }
}
