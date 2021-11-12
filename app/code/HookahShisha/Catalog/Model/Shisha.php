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
 * Shisha Model
 */
class Shisha extends DataObject
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
     * Retrieve array of shisha products
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getShishaProducts(Product $currentProduct): array
    {
        if (!$this->hasShishaProducts()) {
            $products = [];
            $collection = $this->getShishaProductCollection($currentProduct);
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setShishaProducts($products);
        }

        return $this->getData('shisha_products');
    }

    /**
     * Retrieve shisha products identifiers
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getShishaProductIds(Product $currentProduct)
    {
        if (!$this->hasShishaProductIds()) {
            $ids = [];
            foreach ($this->getShishaProducts($currentProduct) as $product) {
                $ids[] = $product->getId();
            }
            $this->setShishaProductIds($ids);
        }

        return $this->getData('shisha_product_ids');
    }

    /**
     * Retrieve collection shisha product
     *
     * @param Product $currentProduct
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function getShishaProductCollection(Product $currentProduct)
    {
        $collection = $this->getLinkInstance()->useShishaLinks()->getProductCollection()->setIsStrongMode();
        $collection->setProduct($currentProduct);

        return $collection;
    }

    /**
     * Retrieve collection shisha link
     *
     * @param Product $currentProduct
     * @return Collection
     */
    public function getShishaLinkCollection(Product $currentProduct)
    {
        $collection = $this->getLinkInstance()->useShishaLinks()->getLinkCollection();
        $collection->setProduct($currentProduct);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();

        return $collection;
    }
}
