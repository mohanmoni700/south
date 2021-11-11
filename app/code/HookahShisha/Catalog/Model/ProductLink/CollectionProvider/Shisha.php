<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Model\ProductLink\CollectionProvider;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;
use HookahShisha\Catalog\Model\Shisha as ShishaModel;

/**
 * Shisha collection provider
 */
class Shisha implements CollectionProviderInterface
{
    /**
     * @var ShishaModel
     */
    protected ShishaModel $shishaModel;

    /**
     * @param ShishaModel $shishaModel
     */
    public function __construct(
        ShishaModel $shishaModel
    ) {
        $this->shishaModel = $shishaModel;
    }

    /**
     * @inheritdoc
     */
    public function getLinkedProducts(Product $product): array
    {
        return $this->shishaModel->getShishaProducts($product);
    }
}
