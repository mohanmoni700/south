<?php

declare(strict_types=1);

namespace HookahShisha\SalesGraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;

class CustomerBalance implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    )
    {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var OrderInterface $order */
        $order = $value['model'];
        $currency = $order->getOrderCurrencyCode();

        return [
            'value' => $order->getCustomerBalanceAmount() ?? 0,
            'currency' => $currency
        ];

    }

}
