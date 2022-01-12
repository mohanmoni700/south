<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SuperPack
 * @author    Bashid
 */
declare(strict_types=1);

namespace HookahShisha\SuperPack\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get SuperPack products
 *
 * Class SuperPackProductAttribute
 */
class SuperPackProduct implements ResolverInterface
{
    private ProductRepositoryInterface $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];
        $product = $this->productRepository->get($product->getSku());
        $flavourSku = $product->getCustomAttribute('flavour_sku');
        $isSuperPack = $product->getCustomAttribute('is_superpack');
        if ($isSuperPack &&
            $isSuperPack->getValue() &&
            $flavourSku &&
            $flavourSku->getValue()
        ) {
            $superPackSku = trim($flavourSku->getValue());
            try {
                $superPackProduct =
                    $this->productRepository->get($superPackSku);
            } catch (\Exception $e) {
                return null;
            }

            if ($superPackProduct) {
                $data = $superPackProduct->getData();
                $data['model'] = $superPackProduct;
                return $data;
            }
        }
        return null;
    }
}
