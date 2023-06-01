<?php
/**
 * class HookahShisha
 * @package HookahShisha_CatalogGraphQl
 */
declare(strict_types=1);
namespace HookahShisha\CatalogGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 *ProductSalableQty  Resolver to get SalableQty of an product
 */
class Saleableqty implements ResolverInterface
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return int
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $product = $value['model'];
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $storeId);
        $quantity = $stockItem->getQty();
        return $quantity??0;

    }
}
