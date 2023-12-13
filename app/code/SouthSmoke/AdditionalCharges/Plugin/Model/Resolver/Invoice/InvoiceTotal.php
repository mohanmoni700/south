<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Plugin\Model\Resolver\Invoice;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Resolver\Invoice\InvoiceTotal as MagentoInvoiceTotal;

class InvoiceTotal
{
    /**
     * Plugin Add additional prices to resolver
     *
     * @param MagentoInvoiceTotal $subject
     * @param array $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoInvoiceTotal $subject,// NOSONAR
        $result,
        Field           $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo     $info, // NOSONAR
        array           $value = null, // NOSONAR
        array           $args = null // NOSONAR
    ) {
        /** @var OrderInterface $orderModel */
        $orderModel = $value['order'];
        /** @var InvoiceInterface $invoiceModel */
        $invoiceModel = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();

        $result['shipping_insurance'] =
            ['value' => $invoiceModel->getShippingInsurance() ?? 0, 'currency' => $currency];
        $result['compliance_insurance'] =
            ['value' => $invoiceModel->getComplianceInsurance() ?? 0, 'currency' => $currency];
        return $result;
    }

}
