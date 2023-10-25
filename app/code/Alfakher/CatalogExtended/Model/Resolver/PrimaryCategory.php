<?php

declare(strict_types=1);

namespace Alfakher\CatalogExtended\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class PrimaryCategory implements ResolverInterface
{
    /**
     * @var Product
     */
    protected Product $productModel;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @param Product $productModel
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Product                     $productModel,
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->productModel = $productModel;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        /** @var Product $product */
        $product = $value['model'];

        $primaryCategoryId = $product->getData('primary_category');

        if ($primaryCategoryId !== null) {
            $category = $this->categoryRepository->get($primaryCategoryId);
            $categoryName = $category->getName();
            $path = $category->getUrlPath();

            return [
                'name' => $categoryName,
                'url_path' => $path
            ];
        }

        return [];
    }
}
