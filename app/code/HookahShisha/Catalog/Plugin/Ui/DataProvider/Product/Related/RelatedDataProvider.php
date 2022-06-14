<?php
declare(strict_types=1);

namespace HookahShisha\Catalog\Plugin\Ui\DataProvider\Product\Related;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Ui\DataProvider\Product\Related\RelatedDataProvider as MagentoRelatedDataProvider;

class RelatedDataProvider extends MagentoRelatedDataProvider
{
    private Visibility $productVisibility;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param Visibility $productVisibility
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        Visibility $productVisibility,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
        $this->productVisibility = $productVisibility;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $productRepository,
            $storeRepository,
            $productLinkRepository,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
    }

    /**
     * Add specific filters
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function addCollectionFilters(Collection $collection)
    {
        $relatedProducts = [];

        /** @var ProductLinkInterface $linkItem */
        foreach ($this->productLinkRepository->getList($this->getProduct()) as $linkItem) {
            if ($linkItem->getLinkType() !== $this->getLinkType()) {
                continue;
            }

            $relatedProducts[] = $this->productRepository->get($linkItem->getLinkedProductSku())->getId();
        }

        if ($relatedProducts) {
            $collection->addAttributeToFilter(
                $collection->getIdFieldName(),
                ['nin' => [$relatedProducts]]
            );
        }

        // Show only simple products
        $collection->addAttributeToFilter(
            'type_id',
            ['eq'=> 'simple']
        );
        
        return $collection;
    }
}
