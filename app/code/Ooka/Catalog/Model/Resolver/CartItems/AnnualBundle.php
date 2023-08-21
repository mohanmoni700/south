<?php

declare(strict_types=1);

namespace Ooka\Catalog\Model\Resolver\CartItems;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AnnualBundle implements ResolverInterface
{

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return bool
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $item = $value['model'] ?? null;

        if (isset($item)) {
             return $item->getProduct()->getData('is_annual_bundle') == 1;
        }
        return false;
    }
}
