<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Alfakher\OutOfStockProduct\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch\ProductCollectionSearchCriteriaBuilder;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch as MagentoProductSearch;

class ProductSearch extends MagentoProductSearch
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionPreProcessor
     * @param CollectionPostProcessor $collectionPostProcessor
     * @param SearchResultApplierFactory $searchResultsApplierFactory
     * @param ProductCollectionSearchCriteriaBuilder $searchCriteriaBuilder
     * @param Visibility $catalogProductVisibility
     */
    public function __construct(
        CollectionFactory                      $collectionFactory,
        ProductSearchResultsInterfaceFactory   $searchResultsFactory,
        CollectionProcessorInterface           $collectionPreProcessor,
        CollectionPostProcessor                $collectionPostProcessor,
        SearchResultApplierFactory             $searchResultsApplierFactory,
        ProductCollectionSearchCriteriaBuilder $searchCriteriaBuilder,
        Visibility                             $catalogProductVisibility
    ) {
        parent::__construct(
            $collectionFactory,
            $searchResultsFactory,
            $collectionPreProcessor,
            $collectionPostProcessor,
            $searchResultsApplierFactory,
            $searchCriteriaBuilder,
            $catalogProductVisibility
        );
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionPreProcessor = $collectionPreProcessor;
        $this->collectionPostProcessor = $collectionPostProcessor;
        $this->searchResultApplierFactory = $searchResultsApplierFactory;
    }

    /**
     *
     * @param SearchResultInterface $searchResult
     * @return false
     */
    public function getUrlKeyIfPDP(SearchResultInterface $searchResult)
    {
        foreach ($searchResult->getSearchCriteria()->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'url_key') {
                    return $filter->getValue();
                }
            }
        }
        return false;
    }

    /**
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultInterface $searchResult
     * @param array $attributes
     * @param ContextInterface|null $context
     * @return SearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        SearchResultInterface   $searchResult,
        array                   $attributes = [],
        ContextInterface        $context = null
    ): SearchResultsInterface {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        //Create a copy of search criteria without filters to preserve the results from search
        $searchCriteriaForCollection = $this->searchCriteriaBuilder->build($searchCriteria);
        //Apply CatalogSearch results from search and join table
        $this->getSearchResultsApplier(
            $searchResult,
            $collection,
            $this->getSortOrderArray($searchCriteriaForCollection)
        )->apply();

        /**
         * if it is pdp it is filtered with urlkey, so when ever we
         * have url key filter we dont do need stock filters
         */
        if ($urlKey = $this->getUrlKeyIfPDP($searchResult)) {
            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter('url_key', $urlKey);
        } else {
            /** Custom code added to sort product collection by stock_status */
            $collection->getSelect()->joinLeft(
                [
                    "stock_data" => "cataloginventory_stock_item"
                ],
                'e.entity_id = stock_data.product_id',
                ['stock_data.is_in_stock']
            );
            $collection->getSelect()->reset('order');
            $collection->getSelect()->order("stock_data.is_in_stock DESC");
            /** Custom code End Here */
        }

        $collection->setFlag('search_resut_applied', true);

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInSiteIds());
        $this->collectionPreProcessor->process($collection, $searchCriteriaForCollection, $attributes, $context);
        $collection->load();
        $this->collectionPostProcessor->process($collection, $attributes);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteriaForCollection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Create searchResultApplier
     *
     * @param SearchResultInterface $searchResult
     * @param Collection $collection
     * @param array $orders
     * @return SearchResultApplierInterface
     */
    private function getSearchResultsApplier(
        SearchResultInterface $searchResult,
        Collection            $collection,
        array                 $orders
    ): SearchResultApplierInterface {
        return $this->searchResultApplierFactory->create(
            [
                'collection' => $collection,
                'searchResult' => $searchResult,
                'orders' => $orders
            ]
        );
    }

    /**
     * Format sort orders into associative array
     *
     * E.g. ['field1' => 'DESC', 'field2' => 'ASC", ...]
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    private function getSortOrderArray(SearchCriteriaInterface $searchCriteria)
    {
        $ordersArray = [];
        $sortOrders = $searchCriteria->getSortOrders();
        if (is_array($sortOrders)) {
            foreach ($sortOrders as $sortOrder) {
                // I am replacing _id with entity_id because in ElasticSearch _id is required for sorting by ID.
                // Where as entity_id is required when using ID as the sort in $collection->load();.
                // @see \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search::getResult
                if ($sortOrder->getField() === '_id') {
                    $sortOrder->setField('entity_id');
                }
                $ordersArray[$sortOrder->getField()] = $sortOrder->getDirection();
            }
        }

        return $ordersArray;
    }
}
