<?php
declare(strict_types=1);

namespace Shishaworld\OrderItemExcludingTaxPrice\Model\Resolver\Invoice;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for Invoice Items
 */
class InvoiceItems implements ResolverInterface
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

        /** @var InvoiceInterface $invoiceModel */
        $invoiceModel = $value['model'];
        return ['invoice_item_including_tax_price' => $invoiceModel->getPriceInclTax()];
    }
}
