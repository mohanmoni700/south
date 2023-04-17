<?php
declare(strict_types=1);

namespace HookahShisha\SalesGraphQl\Plugin\Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesGraphQl\Model\Resolver\OrderTotal as orderTatalResolver;
use Magento\Sales\Api\Data\OrderInterface;

class OrderTotal
{
    /**
     * [afterResolve]
     *
     * @param orderTatalResolver $subject
     * @param array $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @throws LocalizedException
     * @return array
     */
    public function afterResolve(
        orderTatalResolver $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $order = $value['model'];
        if (!empty($order && $order->getSubTotalInclTax())) {
            $result['subtotal_inclusive_tax'] = [
                'value' => $order->getSubTotalInclTax(),
                'currency' => $order->getBaseCurrencyCode(),
            ];
        }
        return $result;
    }
}