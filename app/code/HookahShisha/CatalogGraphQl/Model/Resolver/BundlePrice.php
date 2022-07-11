<?php
declare(strict_types=1);

namespace HookahShisha\CatalogGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get Parent Url key for Related products.
 *
 * Class ProductUrlKey
 */
class BundlePrice implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Product $product */
        $product = $value['model'];
        $discount = $product->getSpecialPrice() ?? 0;
        return [
            "price" =>  $product->getPrice(),
            "final_price" => $product->getFinalPrice(),
            "discount" => $discount
        ];
    }
}
