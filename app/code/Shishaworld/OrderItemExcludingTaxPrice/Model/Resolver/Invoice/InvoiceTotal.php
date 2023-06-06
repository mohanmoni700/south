<?php
declare(strict_types=1);

namespace Shishaworld\OrderItemExcludingTaxPrice\Model\Resolver\Invoice;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesGraphQl\Model\Resolver\Invoice\InvoiceTotal as Total;

/**
 * Resolver for Invoice total
 */
class InvoiceTotal
{
    /**
     * Function afterResolve
     *
     * @param Total $subject
     * @param Array $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function afterResolve(
        Total $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $result['invoice_subtotal_incl_tax_data']['invoice_subtotal_incl_tax'] = $value['model']['subtotal_incl_tax'];
        $result['invoice_subtotal_incl_tax_data']['invoice_subtotal_currency'] = $value['model']['store_currency_code'];

        return $result;
    }
}
