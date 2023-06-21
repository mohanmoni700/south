<?php
declare(strict_types=1);

namespace SouthSmoke\AdditionalCharges\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\CartPrices as MagentoCartPrices;

/**
 * SouthSmoke Additional insurance prices
 */
class CartPrices
{

    /**
     * Plugin Add additional prices to resolver
     *
     * @param MagentoCartPrices $subject
     * @param array $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoCartPrices $subject,// NOSONAR
        $result,
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        $quote = $result['model'];
        $currency = $quote->getQuoteCurrencyCode();

        $result['shipping_insurance'] =
            ['value' => $quote->getShippingInsurance() ?? 0, 'currency' => $currency];
        $result['compliance_insurance'] =
            ['value' => $quote->getComplianceInsurance() ?? 0, 'currency' => $currency];
        return $result;
    }
}
