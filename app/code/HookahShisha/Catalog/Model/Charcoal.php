<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Framework\DataObject;
use HookahShisha\Catalog\Model\Product\Link;

/**
 * Class Charcoal
 */
class Charcoal extends DataObject
{
    /**
     * Product link instance
     *
     * @var Product\Link
     */
    protected $linkInstance;

    /**
     * @param Link $productLink
     * @param array $data
     */
    public function __construct(Link $productLink, array $data = [])
    {
        $this->linkInstance = $productLink;

        parent::__construct($data);
    }

    /**
     * Retrieve link instance
     *
     * @return  Product\Link
     */
    public function getLinkInstance()
    {
        return $this->linkInstance;
    }

    /**
     * Retrieve array of charcoal products
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getCharcoalProducts(Product $currentProduct): array
    {
        if (!$this->hasCharcoalProducts()) {
            $products = [];
            $collection = $this->getCharcoalProductCollection($currentProduct);
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setCharcoalProducts($products);
        }

        return $this->getData('charcoal_products');
    }

    /**
     * Retrieve charcoal products identifiers
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getCharcoalProductIds(Product $currentProduct): array
    {
        if (!$this->hasCharcoalProductIds()) {
            $ids = [];
            foreach ($this->getCharcoalProducts($currentProduct) as $product) {
                $ids[] = $product->getId();
            }
            $this->setCharcoalProductIds($ids);
        }

        return $this->getData('charcoal_product_ids');
    }

    /**
     * Retrieve collection charcoal product
     *
     * @param Product $currentProduct
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getCharcoalProductCollection(Product $currentProduct)
    {
        $collection = $this->getLinkInstance()->useCharcoalLinks()->getProductCollection()->setIsStrongMode();
        $collection->setProduct($currentProduct);

        return $collection;
    }

    /**
     * Retrieve collection charcoal link
     *
     * @param Product $currentProduct
     * @return Collection
     */
    public function getCharcoalLinkCollection(Product $currentProduct)
    {
        $collection = $this->getLinkInstance()->useCharcoalLinks()->getLinkCollection();
        $collection->setProduct($currentProduct);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();

        return $collection;
    }
}
