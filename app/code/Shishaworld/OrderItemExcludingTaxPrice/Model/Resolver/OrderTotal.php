<?php
declare(strict_types=1);

namespace Shishaworld\OrderItemExcludingTaxPrice\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve order totals taxes and discounts for order
 */
class OrderTotal implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        /** @var OrderInterface $order */
        $order = $value['model'];

        return [
            'value' => $order->getSubtotalInclTax(),
            'currency' => $order->getOrderCurrencyCode()
        ];
    }
}
