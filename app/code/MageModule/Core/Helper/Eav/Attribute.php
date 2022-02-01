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

namespace MageModule\Core\Helper\Eav;

use Magento\Eav\Api\Data\AttributeInterface;

/**
 * Class Attribute
 *
 * @package MageModule\Core\Helper\Eav
 */
class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $selectAttributeCodes;

    /**
     * @var array
     */
    private $multiselectAttributeCodes;

    /**
     * Attribute constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param \Magento\Framework\App\ResourceConnection                    $resource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * For a specific attribute/object, returns array of stores IDs which have
     * an actual value saved in the database, in that store's scope. For non-static,
     * store-scoped or website-scoped attributes only. Otherwise returns empty array.
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param \Magento\Framework\Model\AbstractModel                $object
     *
     * @return int[]
     */
    public function getStoreIdsHavingAttributeValue(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $result = [];
        if ($object->getId() && !$attribute->isStatic()) {
            $table       = $attribute->getBackendTable();
            $connection  = $this->resource->getConnection('core_read');
            $description = $connection->describeTable($table);
            if (isset($description['store_id'])) {
                $select = $connection->select()->from($table, 'store_id');
                $select->where(AttributeInterface::ATTRIBUTE_ID . ' = ?', $attribute->getAttributeId());
                $select->where($attribute->getEntityIdField() . ' = ?', $object->getId());
                $result = $connection->fetchCol($select);
            }
        }

        return $result;
    }

    /**
     * @param string|null $entityTypeCode
     *
     * @return array
     */
    public function getSelectAttributeCodes($entityTypeCode = null)
    {
        if ($this->selectAttributeCodes === null) {
            /** @var \Magento\Eav\Model\ResourceModel\Attribute\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('frontend_input', 'select');
            $collection->join(
                ['eav_entity_type_table' => $collection->getTable('eav_entity_type')],
                'main_table.entity_type_id = eav_entity_type_table.entity_type_id',
                'entity_type_id'
            );

            if ($entityTypeCode) {
                $collection->addFieldToFilter('eav_entity_type_table.entity_type_code', $entityTypeCode);
            }

            $this->selectAttributeCodes = $collection->getColumnValues('attribute_code');
        }

        return $this->selectAttributeCodes;
    }

    /**
     * @param string|null $entityTypeCode
     *
     * @return array
     */
    public function getMultiselectAttributeCodes($entityTypeCode = null)
    {
        if ($this->multiselectAttributeCodes === null) {
            /** @var \Magento\Eav\Model\ResourceModel\Attribute\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('frontend_input', 'multiselect');
            $collection->join(
                ['eav_entity_type_table' => $collection->getTable('eav_entity_type')],
                'main_table.entity_type_id = eav_entity_type_table.entity_type_id',
                'entity_type_id'
            );

            if ($entityTypeCode) {
                $collection->addFieldToFilter('eav_entity_type_table.entity_type_code', $entityTypeCode);
            }

            $this->multiselectAttributeCodes = $collection->getColumnValues('attribute_code');
        }

        return $this->multiselectAttributeCodes;
    }
}
