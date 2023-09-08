<?php

declare(strict_types=1);

namespace Alfakher\GiftProduct\Pricing\Price;

use Magento\GiftCard\Pricing\Price\FinalPrice as MagentoFinalPrice;

class FinalPrice extends MagentoFinalPrice
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
            $this->amountsCache[] = $this->priceCurrency->convertAndRound($this->product->getOpenAmountMin());
        }

        sort($this->amountsCache);
        return $this->amountsCache;
    }
}
