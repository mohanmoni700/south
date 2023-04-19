<?php
declare(strict_types=1);

namespace HookahShisha\SalesGraphQl\Plugin\Magento\SalesGraphQl\Model\Resolver\Invoice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesGraphQl\Model\Resolver\Invoice\InvoiceTotal as invoiceTotalResolver;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

class InvoiceTotal
{
    /**
     * [afterResolve]
     *
     * @param invoiceTotalResolver $subject
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
        invoiceTotalResolver $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }
        if (!(($value['model'] ?? null) instanceof InvoiceInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $orderModel = $value['order'];
        $invoiceModel = $value['model'];
        if (!empty($invoiceModel && $invoiceModel->getSubTotalInclTax())) {
            $result['subtotal_inclusive_tax'] = [
                'value' => $invoiceModel->getSubTotalInclTax(),
                'currency' => $orderModel->getBaseCurrencyCode(),
            ];
        }
        return $result;
    }
}
