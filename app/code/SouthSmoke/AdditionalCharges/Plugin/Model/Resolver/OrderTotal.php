<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Resolver\OrderTotal as MagentoOrderTotal;

class OrderTotal
{
    /**
     * Plugin Add additional prices to resolver
     *
     * @param MagentoOrderTotal $subject
     * @param array $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoOrderTotal $subject,// NOSONAR
        $result,
        Field           $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo     $info, // NOSONAR
        array           $value = null, // NOSONAR
        array           $args = null // NOSONAR
    ) {
        /** @var OrderInterface $order */
        $order = $value['model'];
        $currency = $order->getOrderCurrencyCode();

        $result['shipping_insurance'] =
            ['value' => $order->getShippingInsurance() ?? 0, 'currency' => $currency];
        $result['compliance_insurance'] =
            ['value' => $order->getComplianceInsurance() ?? 0, 'currency' => $currency];

        return $result;
    }

}
