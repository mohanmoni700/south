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

        return [
            'model' => $cartItem,
            'price' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getCalculationPrice(),
            ],
            'row_total' => [
                'currency' => $currencyCode,
                'value' => $cartItem->getRowTotal() + $this->getBundleProductTotals($cartItem)
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
     * Returns totals for bundle products
     *
     * @param Item $cartItem
     * @return int
     */
    private function getBundleProductTotals(Item $cartItem): int
    {
        $alfaBundle = $cartItem->getAlfaBundle();
        $shishaSku = '';
        $charcoalSku = '';
        $total = 0;

        if ($alfaBundle) {
            $alfaBundle = json_decode($alfaBundle, true);
            $shishaSku = $alfaBundle['shisha_sku'];
            $charcoalSku = $alfaBundle['charcoal_sku'];
        }

        if (!$shishaSku && !$charcoalSku) {
            return $total;
        }

        foreach ($cartItem->getQuote()->getAllItems() as $item) {
            if ($item->getSku() == $shishaSku || $item->getSku() == $charcoalSku) {
                $total += $item->getCalculationPrice() * $cartItem->getQty();
            }
        }

        return $total;
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
