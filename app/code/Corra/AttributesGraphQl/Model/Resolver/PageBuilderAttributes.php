<?php
declare(strict_types=1);

namespace Corra\AttributesGraphQl\Model\Resolver;

use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Widget\Model\Template\FilterEmulate;

class PageBuilderAttributes implements ResolverInterface
{

    private $widgetFilter;

    /**
     * @param FilterEmulate $widgetFilter
     */
    public function __construct(
        FilterEmulate $widgetFilter
    ) {
        $this->widgetFilter = $widgetFilter;
    }

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

        $product = $value['model'];
        $attributeCode = $field->getName();
        if ($attributeCode) {
            return $this->widgetFilter->filter($product->getData($attributeCode));
        }
        return '';
    }
}
