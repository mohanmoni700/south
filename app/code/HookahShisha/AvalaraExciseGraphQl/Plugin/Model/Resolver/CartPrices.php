<?php
declare(strict_types=1);

namespace HookahShisha\AvalaraExciseGraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\CartPrices as MagentoCartPrices;

/**
 * Avalara Tax setting in the Applied Taxes
 */
class CartPrices
{
    /**
     * Avalara Tax setting in the Applied Taxes
     *
     * @param MagentoCartPrices $subject
     * @param array $result
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return mixed
     *
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        MagentoCartPrices $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($result['applied_taxes'])) {
            //ToDO change harcoded value: presently checking how we can take the tax amount same as luma
            $result['applied_taxes'][] = [
                'label' => 1,
                'amount' => ['value' => 2.79, 'currency' => 'USD']
            ];
        }
        return $result;
    }
}
