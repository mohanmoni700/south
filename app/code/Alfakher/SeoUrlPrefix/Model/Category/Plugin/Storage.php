<?php

namespace Alfakher\SeoUrlPrefix\Model\Category\Plugin;

use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;

class Storage extends \Magento\CatalogUrlRewrite\Model\Category\Plugin\Storage
{
    /**
     * @var Product
     */
    protected $productResource;

    /**
     * Storage constructor.
     * @param UrlFinderInterface $urlFinder
     * @param Product $productResource
     */
    public function __construct(UrlFinderInterface $urlFinder, Product $productResource)
    {
        parent::__construct($urlFinder, $this->productResource = $productResource);
    }
    /**
     * Save product/category urlRewrite association
     *
     * @param StorageInterface $object
     * @param array $result
     * @param array $urls
     * @return array|\Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function afterReplace(StorageInterface $object, array $result, array $urls)
    {
        $toSave = [];
        foreach ($this->filterUrls($result) as $record) {
            $metadata = $record->getMetadata();
            if (!empty($metadata)) {
                $toSave[] = [
                    'url_rewrite_id' => $record->getUrlRewriteId(),
                    'category_id' => $metadata['category_id'],
                    'product_id' => $record->getEntityId(),
                ];
            }
        }
        if (count($toSave) > 0) {
            $this->productResource->saveMultiple($toSave);

        }
        return $result;
    }
}
