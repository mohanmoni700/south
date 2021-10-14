<?php
declare(strict_types=1);

namespace HookahShisha\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DetailsTab implements ResolverInterface
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @param OutputHelper $outputHelper
     */
    public function __construct(
        OutputHelper $outputHelper
    ) {
        $this->outputHelper = $outputHelper;
    }

    /**
     * Get html output
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|string
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /* @var $product Product */
        $product = $value['model'];
        return $this->outputHelper->productAttribute($product, $product->getData('details_tab'), 'details_tab');
    }
}
