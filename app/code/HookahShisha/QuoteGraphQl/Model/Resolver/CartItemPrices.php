<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\QuoteGraphQl\Model\Resolver\CartItemPrices as SourceCartItemPrices;

class CartItemPrices extends SourceCartitemPrices
{
    /**
     * @var TotalsCollector
     */
    private TotalsCollector $totalsCollector;

    /**
     * @var Totals
     */
    private $totals;

    /**
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(TotalsCollector $totalsCollector)
    {
        parent::__construct($totalsCollector);

        $this->totalsCollector = $totalsCollector;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Item $cartItem */
        $cartItem = $value['model'];

        if (!$this->totals) {
            // The totals calculation is based on quote address.
            // But the totals should be calculated even if no address is set
            $this->totals = $this->totalsCollector->collectQuoteTotals($cartItem->getQuote());
        }

        $currencyCode = $cartItem->getQuote()->getQuoteCurrencyCode();

        if (isset($value['super_pack_models']) && $value['super_pack_models']) {
            return $this->getCartItemPricesForSuperpack(
                $cartItem,
                $value['super_pack_models'],
                $currencyCode
            );
        }

        return [
            'model' => $cartItem,
            'price' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getCalculationPrice(),
            ],
            'row_total' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getRowTotal()
            ],
            'row_total_including_tax' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getRowTotalInclTax(),
            ],
            'total_item_discount' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getDiscountAmount(),
            ],
            'discounts' => $this->getDiscountValues($cartItem, $currencyCode)
        ];
    }

    /**
     * Get item prices for superpack
     *
     * @param Item $simpleProductCartItem
     * @param Item[] $superPackModels
     * @param string $currencyCode
     * @return array
     */
    private function getCartItemPricesForSuperpack(
        Item $simpleProductCartItem,
        array $superPackModels,
        string $currencyCode
    ) {
        $price = 0;
        $rowTotal = 0;
        $rowTotalIncludingTax = 0;
        $discountAmount = 0;
        $superPackModels[] = $simpleProductCartItem;
        foreach ($superPackModels as $cartItem) {
            $price += $cartItem->getCalculationPrice() ?? 0;
            $rowTotal += $cartItem->getRowTotal() ?? 0;
            $rowTotalIncludingTax += $cartItem->getRowTotal() ?? 0;
            $discountAmount += $cartItem->getDiscountAmount() ?? 0;
        }

        return [
            'model' => $simpleProductCartItem,
            'price' => [
                'currency' => $currencyCode,
                'value' => $price,
            ],
            'row_total' => [
                'currency' => $currencyCode,
                'value' => $rowTotal
            ],
            'row_total_including_tax' => [
                'currency' => $currencyCode,
                'value' => $rowTotalIncludingTax,
            ],
            'total_item_discount' => [
                'currency' => $currencyCode,
                'value' => $discountAmount,
            ],
            'discounts' => $this->getDiscountValues($simpleProductCartItem, $currencyCode)
        ];
    }

    /**
     * Get Discount Values
     *
     * @param Item $cartItem
     * @param string $currencyCode
     * @return array
     */
    private function getDiscountValues(Item $cartItem, string $currencyCode): ?array
    {
        $itemDiscounts = $cartItem->getExtensionAttributes()->getDiscounts();
        if ($itemDiscounts) {
            $discountValues=[];
            foreach ($itemDiscounts as $value) {
                $discount = [];
                $amount = [];
                $discountData = $value->getDiscountData();
                $discountAmount = $discountData->getAmount();
                $discount['label'] = $value->getRuleLabel() ?: __('Discount');
                $amount['value'] = $discountAmount;
                $amount['currency'] = $currencyCode;
                $discount['amount'] = $amount;
                $discountValues[] = $discount;
            }
            return $discountValues;
        }
        return null;
    }
}
