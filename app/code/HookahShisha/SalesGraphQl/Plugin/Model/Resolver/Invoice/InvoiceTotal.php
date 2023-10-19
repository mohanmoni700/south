<?php

declare (strict_types=1);

namespace HookahShisha\SalesGraphQl\Plugin\Model\Resolver\Invoice;

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
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        /** @var OrderInterface $orderModel */
        $orderModel = $value['order'];
        /** @var InvoiceInterface $invoiceModel */
        $invoiceModel = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();

        $result['store_credit'] =
            ['value' => $invoiceModel->getCustomerBalanceAmount() ?? 0, 'currency' => $currency];
        return $result;
    }
}
