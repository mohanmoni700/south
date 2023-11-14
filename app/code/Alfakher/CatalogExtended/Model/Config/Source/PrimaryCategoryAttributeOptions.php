<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductRepository;

class PrimaryCategoryAttributeOptions extends AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param RequestInterface $request
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        RequestInterface  $request,
        ProductRepository $productRepository
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    public function getAllOptions(): ?array
    {
        $categoryCollection = $this->getCategoryTree();
        if ($this->_options === null) {
            $this->_options = $categoryCollection;
        }
        return $this->_options;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getCategoryTree(): array
    {
        $productId = $this->request->getParam('id');
        $productCategoryIds = "";
        if (isset($productId)) {
            $productCategoryIds = $this->productRepository->getById($productId)->getCategoryIds();
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name')->addIdFilter($productCategoryIds);
        foreach ($collection as $category) {
            $categoryId = $category->getEntityId();
            $categoryById[] = [
                'label' => $category->getName(),
                'value' => $categoryId
            ];
        }

        if (empty($categoryById)) {
            $categoryById[] = [
                'label' => 'Product has no categories bound to it',
                'value' => null
            ];
        }

        $categoryName = array_column($categoryById, 'label');
        array_multisort($categoryName, SORT_ASC, $categoryById);
        array_unshift($categoryById, [
            'label' => 'None',
            'value' => null
        ]);

        return $categoryById;
    }
}
