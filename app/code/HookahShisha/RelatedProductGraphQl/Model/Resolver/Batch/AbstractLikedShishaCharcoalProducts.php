<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_RelatedProductGraphQl
 * @author    Janis Verins <info@corra.com
 */
declare(strict_types=1);

namespace HookahShisha\RelatedProductGraphQl\Model\Resolver\Batch;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Resolve shisha and charcoal linked product lists.
 */
abstract class AbstractLikedShishaCharcoalProducts implements BatchResolverInterface
{
    /**
     * @var ProductFieldsSelector
     */
    private ProductFieldsSelector $productFieldsSelector;

    /**
     * @var RelatedProductDataProvider
     */
    private RelatedProductDataProvider $relatedProductDataProvider;

    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param RelatedProductDataProvider $relatedProductDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        RelatedProductDataProvider $relatedProductDataProvider,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productFieldsSelector = $productFieldsSelector;
        $this->relatedProductDataProvider = $relatedProductDataProvider;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Node type.
     *
     * @return string
     */
    abstract protected function getNode(): string;

    /**
     * Type of linked products to be resolved.
     *
     * @return int
     */
    abstract protected function getLinkType(): int;

    /**
     * Find related products.
     *
     * @param ProductInterface[] $products
     * @param string[] $loadAttributes
     * @param int $linkType
     * @return ProductInterface[][]
     */
    private function findRelations(array $products, array $loadAttributes, int $linkType): array
    {
        //Loading relations
        $relations = $this->relatedProductDataProvider->getRelations($products, $linkType);
        if (!$relations) {
            return [];
        }
        $relatedIds = array_unique(array_merge([], ...array_values($relations)));
        //Loading products data.
        $this->searchCriteriaBuilder->addFilter('entity_id', $relatedIds, 'in');
        $relatedSearchResult = $this->productDataProvider->getList(
            $this->searchCriteriaBuilder->create(),
            $loadAttributes
        );
        //Filling related products map.
        /** @var ProductInterface[] $relatedProducts */
        $relatedProducts = [];
        /** @var ProductInterface $item */
        foreach ($relatedSearchResult->getItems() as $item) {
            $relatedProducts[$item->getId()] = $item;
        }

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $productId => $relatedIds) {
            //Remove related products that not exist in map list.
            $relatedIds = array_filter($relatedIds, function ($relatedId) use ($relatedProducts) {
                return isset($relatedProducts[$relatedId]);
            });
            $relationsData[$productId] = array_map(
                function ($id) use ($relatedProducts) {
                    return $relatedProducts[$id];
                },
                $relatedIds
            );
        }

        return $relationsData;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        /** @var ProductInterface[] $products */
        $products = [];
        $fields = [];
        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            //Gathering fields and relations to load.
            if (empty($request->getValue()['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $request->getValue()['model'];
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }
        $fields = array_unique(array_merge([], ...$fields));

        //Finding relations.
        $related = $this->findRelations($products, $fields, $this->getLinkType());

        //Matching requests with responses.
        $response = new BatchResponse();
        /** @var BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            /** @var ProductInterface $product */
            $product = $request->getValue()['model'];
            $result = [];
            if (array_key_exists($product->getId(), $related)) {
                $result = array_map(
                    function ($relatedProduct) {
                        $data = $relatedProduct->getData();
                        $data['model'] = $relatedProduct;

                        return $data;
                    },
                    $related[$product->getId()]
                );
            }
            $response->addResponse($request, $result);
        }

        return $response;
    }
}
