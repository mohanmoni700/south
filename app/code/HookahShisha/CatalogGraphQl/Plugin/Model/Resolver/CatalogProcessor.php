<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    CORRA
 */
declare(strict_types=1);

namespace HookahShisha\CatalogGraphQl\Plugin\Model\Resolver;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessor\CatalogProcessor
    as SourceCatalogProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;

class CatalogProcessor
{
    /**
     * Plugin to fix category filtration
     *
     * @param SourceCatalogProcessor $subject
     * @param Collection $result
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function afterProcess(
        SourceCatalogProcessor $subject, // NOSONAR
        Collection $result,
        Collection $collection, // NOSONAR
        SearchCriteriaInterface $searchCriteria, // NOSONAR
        array $attributeNames, // NOSONAR
        ContextInterface $context = null
    ): Collection {
        $store = $context->getExtensionAttributes()->getStore();
        $this->addRootCategoryFilterForStore($result, (string) $store->getRootCategoryId());

        return $result;
    }

    /**
     * Add filtration based on the store root category id
     *
     * @param Collection $collection
     * @param string $rootCategoryId
     */
    private function addRootCategoryFilterForStore(Collection $collection, string $rootCategoryId) : void
    {
        $select = $collection->getSelect();
        $connection = $collection->getConnection();
        $select->where(
            $connection->quoteInto(
                'e.path LIKE ? OR e.entity_id=' . $connection->quote($rootCategoryId, 'int'),
                '%/' . $rootCategoryId . '/%'
            )
        );
    }
}
