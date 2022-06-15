<?php
declare(strict_types=1);

namespace HookahShisha\CatalogGraphQl\Model\Resolver\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get Parent Url key for Related products.
 *
 * Class ProductUrlKey
 */
class ProductUrlKey implements ResolverInterface
{
    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Configurable $configurable,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
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
    ): ?string {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        if (isset($value['related_products'])) {
            $parentConfigObject = $this->configurable->getParentIdsByChild($product->getId());
            if ($parentConfigObject) {
                return $this->productRepository->getById($parentConfigObject[0])->getUrlKey();
            }
        }
        return $product['url_key'];
    }
}
