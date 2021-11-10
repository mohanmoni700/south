<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Model\ProductLink\CollectionProvider;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;
use HookahShisha\Catalog\Model\Charcoal as CharcoalModel;

/**
 * Class CharcoalModel
 */
class Charcoal implements CollectionProviderInterface
{
    /**
     * @var CharcoalModel
     */
    protected CharcoalModel $charcoalModel;

    /**
     * @param CharcoalModel $charcoalModel
     */
    public function __construct(
        CharcoalModel $charcoalModel
    ) {
        $this->charcoalModel = $charcoalModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedProducts(Product $product): array
    {
        return $this->charcoalModel->getCharcoalProducts($product);
    }
}
