<?php
declare(strict_types=1);

namespace Shishaworld\OrderItemExcludingTaxPrice\Model\Resolver\Invoice;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolver for Invoice Items
 */
class InvoiceItems implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * OrderItem constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

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

        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode() ??
                    $this->storeManager->getStore()->getDefaultCurrencyCode();

        return [
            'value' => $invoiceModel->getPriceInclTax(),
            'currency' => $currency
        ];
    }
}
