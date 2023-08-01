<?php
declare(strict_types=1);

namespace Shishaworld\OrderItemExcludingTaxPrice\Model\Resolver\ShippingAddress;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SelectedShippingMethod;

/**
 * SelectedShippingMethodPlugin
 */
class SelectedShippingMethodPlugin
{
    /**
     * @param SelectedShippingMethod $subject
     * @param $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function afterResolve(
        SelectedShippingMethod $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!empty($result)) {
            // Modified the $result array
            $result['amount_incl_tax']['value'] = $value['model']->getShippingInclTax() ?? null;
            $result['amount_incl_tax']['currency'] = $value['model']->getQuote()->getQuoteCurrencyCode() ?? null;
        }

        return $result;
    }
}
