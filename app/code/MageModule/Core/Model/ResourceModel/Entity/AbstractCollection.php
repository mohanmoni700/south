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
       */ /** @noinspection MessDetectorValidationInspection */

namespace MageModule\Core\Model\ResourceModel\Entity;

/**
 * Class AbstractCollection
 *
 * @package MageModule\Core\Model\ResourceModel\Entity
 */
abstract class AbstractCollection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * AbstractCollection constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\Data\Collection\EntityFactory             $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Eav\Model\Config                                    $eavConfig
     * @param \Magento\Framework\App\ResourceConnection                    $resource
     * @param \Magento\Eav\Model\EntityFactory                             $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper                      $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory                $universalFactory
     * @param null|string                                                  $connection
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );

        $this->storeManager = $storeManager;
    }

    /**
     * @param $store
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStore($store)
    {
        $this->setStoreId($this->storeManager->getStore($store)->getId());

        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Api\Data\StoreInterface $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Api\Data\StoreInterface) {
            $storeId = $storeId->getId();
        }

        $this->storeId = (int)$storeId;

        return $this;
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }
        return $this->storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @param string    $table
     * @param array|int $attributeIds
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        $storeId    = $this->getStoreId();
        $connection = $this->getConnection();

        $entityTable   = $this->getEntity()->getEntityTable();
        $indexList     = $connection->getIndexList($entityTable);
        $entityIdField = $indexList[$connection->getPrimaryKeyName($entityTable)]['COLUMNS_LIST'][0];

        if ($storeId) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$entityIdField} = t_d.{$entityIdField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select = $connection->select()->from(
                ['t_d' => $table],
                ['attribute_id']
            )->join(
                ['e' => $entityTable],
                "e.{$entityIdField} = t_d.{$entityIdField}",
                ['e.entity_id']
            )->where(
                "e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                't_d.attribute_id IN (?)',
                $attributeIds
            )->joinLeft(
                ['t_s' => $table],
                implode(' AND ', $joinCondition),
                []
            )->where(
                't_d.store_id = ?',
                $connection->getIfNullSql('t_s.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
        } else {
            $select = $connection->select()->from(
                ['t_d' => $table],
                ['attribute_id']
            )->join(
                ['e' => $entityTable],
                "e.{$entityIdField} = t_d.{$entityIdField}",
                ['e.entity_id']
            )->where(
                "e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                'attribute_id IN (?)',
                $attributeIds
            )->where(
                'store_id = ?',
                $this->getDefaultStoreId()
            );
        }

        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string                       $table
     * @param string                       $type
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            $connection = $this->getConnection();
            $valueExpr = $connection->getCheckSql(
                't_s.value_id IS NULL',
                't_d.value',
                't_s.value'
            );

            $select->columns(
                ['default_value' => 't_d.value', 'store_value' => 't_s.value', 'value' => $valueExpr]
            );
        } else {
            $select = parent::_addLoadAttributesSelectValues($select, $table, $type);
        }

        return $select;
    }/** @noinspection PhpTooManyParametersInspection */

    /**
     * @param string $method
     * @param object $attribute
     * @param string $tableAlias
     * @param array $condition
     * @param string $fieldCode
     * @param string $fieldAlias
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @noinspection PhpTooManyParametersInspection
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $storeId = $this->_joinAttributes[$fieldCode]['store_id'];
        } else {
            $storeId = $this->getStoreId();
        }

        $connection = $this->getConnection();

        if ($storeId != $this->getDefaultStoreId() && !$attribute->isScopeGlobal()) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '(' . implode(') AND (', $condition) . ')';
            $defAlias = $tableAlias . '_default';
            $defAlias = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias = str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias = $this->getConnection()->getTableName($tableAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition .= $connection->quoteInto(
                " AND " . $connection->quoteColumnAs("{$defAlias}.store_id", null) . " = ?",
                $this->getDefaultStoreId()
            );

            $this->getSelect()->{$method}(
                [$defAlias => $attribute->getBackend()->getTable()],
                $defCondition,
                []
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql(
                "{$tableAlias}.value_id > 0",
                $fieldAlias,
                $defFieldAlias
            );
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute'] = $attribute;
        } else {
            $storeId = $this->getDefaultStoreId();
        }
        $condition[] = $connection->quoteInto(
            $connection->quoteColumnAs("{$tableAlias}.store_id", null) . ' = ?',
            $storeId
        );

        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }
}
