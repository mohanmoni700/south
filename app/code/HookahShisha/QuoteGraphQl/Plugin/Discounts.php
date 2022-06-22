<?php

namespace HookahShisha\QuoteGraphQl\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Resolver\Discounts as MagentoDiscounts;

/**
 * Discount plugin
 */
class Discounts
{
    /**
     * @inheritdoc
     */
    public function aroundResolve(
        MagentoDiscounts $subject, // NOSONAR
        callable $proceed, // NOSONAR
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null,
        array $args = null // NOSONAR
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $quote = $value['model'];

        return $this->getDiscountValues($quote);
    }

    /**
     * Get Total Discount From Cart
     *
     * @param Quote $quote
     * @return array | null
     */
    private function getDiscountValues(Quote $quote)
    {
        $address = $quote->getShippingAddress();
        if ($discount = $address->getDiscountAmount()) {
            return [
                [
                    'amount' => [
                        'label' => __('Discount'),
                        'value' => $discount * -1,
                        'currency' => $quote->getQuoteCurrencyCode()
                    ]
                ]
            ];
        }
        return null;
    }
}
