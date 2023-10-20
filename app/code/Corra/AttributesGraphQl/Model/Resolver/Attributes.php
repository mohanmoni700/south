<?php
/**
 * @author CORRA
 */
declare(strict_types=1);

namespace Corra\AttributesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

/**
 * Get Brand Name
 */
class Attributes implements ResolverInterface
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
        $attributeCode = $field->getName();
        if ($attributeCode) {
            // eg: color_text should be converted to color
            $attributeCode = str_replace('_text', '', $attributeCode);
            $fontendModel = $product->getResource()->getAttribute($attributeCode);
            if ($fontendModel) {
                return $attributeText = $fontendModel->getFrontend()->getValue($product);
            }

        }
        return '';
    }
}
