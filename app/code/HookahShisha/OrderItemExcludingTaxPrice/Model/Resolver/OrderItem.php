<?php
declare(strict_types=1);

namespace HookahShisha\OrderItemExcludingTaxPrice\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve a single order item
 */
class OrderItem implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $parentItem = $value['model'];
        return ['item_including_tax_price' => $parentItem->getOriginalPrice()];
    }
}
