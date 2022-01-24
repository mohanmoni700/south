<?php /**
       * Copyright (c) 2018 MageModule, LLC: All rights reserved
       *
       * LICENSE: This source file is subject to our standard End User License
       * Agreeement (EULA) that is available through the world-wide-web at the
       * following URI: https://www.magemodule.com/end-user-license-agreement/.
       *
       *  If you did not receive a copy of the EULA and are unable to obtain it through
       *  the web, please send a note to admin@magemodule.com so that we can mail
       *  you a copy immediately.
       *
       * @author        MageModule admin@magemodule.com
       * @copyright    2018 MageModule, LLC
       * @license        https://www.magemodule.com/end-user-license-agreement/
       */ /** @noinspection PhpCSValidationInspection */

namespace MageModule\Core\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;

/**
 * Class ScopeOverriddenValue
 *
 * @package MageModule\Core\Model\Entity\Attribute
 */
class ScopeOverriddenValue
{
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var array
     */
    private $attributesValues;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * ScopeOverriddenValue constructor.
     *
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\Api\SearchCriteriaBuilder  $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder          $filterBuilder
     * @param \Magento\Framework\App\ResourceConnection     $resourceConnection
     */
    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->metadataPool          = $metadataPool;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->resourceConnection    = $resourceConnection;
    }

    /**
     * @param string                                 $entityType
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param string                                 $attributeCode
     * @param int                                    $storeId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function containsValue($entityType, $entity, $attributeCode, $storeId)
    {
        if ((int)$storeId === Store::DEFAULT_STORE_ID) {
            return false;
        }
        if ($this->attributesValues === null) {
            $this->initAttributeValues($entityType, $entity, (int)$storeId);
        }

        return isset($this->attributesValues[$storeId])
               && array_key_exists($attributeCode, $this->attributesValues[$storeId]);
    }

    /**
     * @param string                                 $entityType
     * @param \Magento\Framework\Model\AbstractModel $entity
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultValues($entityType, $entity)
    {
        if ($this->attributesValues === null) {
            $this->initAttributeValues($entityType, $entity, (int)$entity->getStoreId());
        }

        return isset($this->attributesValues[Store::DEFAULT_STORE_ID])
            ? $this->attributesValues[Store::DEFAULT_STORE_ID]
            : [];
    }

    /**
     * @param string                                 $entityType
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param int                                    $storeId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    private function initAttributeValues($entityType, $entity, $storeId)
    {
        $metadata   = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attributeTables = [];
        if ($metadata->getEavEntityType()) {
            foreach ($this->getAttributes($entityType) as $attribute) {
                if (!$attribute->isStatic()) {
                    $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
                }
            }
            $storeIds = [Store::DEFAULT_STORE_ID];
            if ($storeId !== Store::DEFAULT_STORE_ID) {
                $storeIds[] = $storeId;
            }
            $selects = [];
            foreach ($attributeTables as $attributeTable => $attributeCodes) {
                $select    = $connection->select()
                    ->from(['t' => $attributeTable], ['value' => 't.value', 'store_id' => 't.store_id'])
                    ->join(
                        ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                        'a.attribute_id = t.attribute_id',
                        ['attribute_code' => 'a.attribute_code']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entity->getData($metadata->getLinkField()))
                    ->where('t.attribute_id IN (?)', $attributeCodes)
                    ->where('t.store_id IN (?)', $storeIds);
                $selects[] = $select;
            }

            $unionSelect = new \Magento\Framework\DB\Sql\UnionExpression(
                $selects,
                \Magento\Framework\DB\Select::SQL_UNION_ALL
            );

            $attributes = $connection->fetchAll((string)$unionSelect);
            foreach ($attributes as $attribute) {
                $this->attributesValues[$attribute['store_id']][$attribute['attribute_code']] = $attribute['value'];
            }
        }
    }

    /**
     * @param string $entityType
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @throws \Exception
     */
    private function getAttributes($entityType)
    {
        $metadata     = $this->metadataPool->getMetadata($entityType);
        $searchResult = $this->attributeRepository->getList(
            $metadata->getEavEntityType(),
            $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField('is_global')
                        ->setConditionType('in')
                        ->setValue(
                            [
                                ScopedAttributeInterface::SCOPE_STORE,
                                ScopedAttributeInterface::SCOPE_WEBSITE
                            ]
                        )
                        ->create()
                ]
            )->create()
        );
        return $searchResult->getItems();
    }
}
