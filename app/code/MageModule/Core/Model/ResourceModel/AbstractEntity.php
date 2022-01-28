<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Helper\Data as CoreHelper;
use MageModule\Core\Helper\Store as StoreHelper;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use MageModule\Core\Model\AbstractExtensibleModel;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractEntity
 *
 * @package MageModule\Core\Model\ResourceModel
 */
abstract class AbstractEntity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * @var CoreHelper
     */
    protected $_helper;

    /**
     * @var StoreHelper
     */
    protected $_storeHelper;

    /**
     * @var EntityManager
     */
    protected $_entityManager;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $staticAttributeValuesToSave = [];

    /**
     * @var array
     */
    private $tablePrimaryFields = [];

    /**
     * @param AbstractModel $object
     * @param int           $entityId
     * @param array|null    $attributes
     *
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row    = $this->getConnection()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesForObject($attributes, $object);
        $this->_entityManager->load($object, $entityId);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     */
    protected function _afterLoad(DataObject $object)
    {
        parent::_afterLoad($object);
        if ($object instanceof AbstractModel) {
            foreach ($object->getData() as $key => $value) {
                $object->setOrigData($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param  AbstractModel $object
     *
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->getData(AbstractExtensibleModel::ATTRIBUTE_SET_ID)) {
            $object->setData(AbstractExtensibleModel::ATTRIBUTE_SET_ID, $this->getDefaultAttributeSetId());
        }

        if (!$object->hasData(AbstractExtensibleModel::STORE_ID)) {
            $object->setData(AbstractExtensibleModel::STORE_ID, Store::DEFAULT_STORE_ID);
        }

        if ($object->isObjectNew()) {
            /**
             * do not remove this line
             * without it, when using entity manager,
             * attribute backend beforeSave function will not be called
             */
            $this->loadAllAttributes($object);
        }

        $this->prepareUseDefaults($object);
        $this->_entityManager->save($object);

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws \Exception
     */
    public function delete($object)
    {
        $this->_entityManager->delete($object);

        return $this;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * @return int[]
     */
    public function getAllIds()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            $this->getEntityTable(),
            $this->getEntityIdField()
        );

        return $connection->fetchCol($select);
    }

    /**
     * @param DataObject                 $object
     * @param AbstractAttribute          $attribute
     * @param int|float|bool|string|null $value
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _insertStaticAttribute($object, $attribute, $value)
    {
        return $this->_updateStaticAttribute($object, $attribute, $value);
    }

    /**
     * @param DataObject                 $object
     * @param AbstractAttribute          $attribute
     * @param int|float|bool|string|null $value
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _updateStaticAttribute($object, $attribute, $value)
    {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->staticAttributeValuesToSave[$table])) {
            $this->staticAttributeValuesToSave[$table] = [];
        }

        $idField    = $attribute->getBackend()->getEntityIdField();
        $valueField = $attribute->getAttributeCode();

        $data = [
            $idField    => $object->getData($idField),
            $valueField => $this->_prepareStaticValue($valueField, $value)
        ];

        $this->staticAttributeValuesToSave[$table][] = $data;

        return $this;
    }

    /**
     * @param DataObject        $object
     * @param AbstractAttribute $attribute
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _deleteStaticAttribute($object, $attribute)
    {
        $this->_updateStaticAttribute($object, $attribute, null);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _processAttributeValues()
    {
        parent::_processAttributeValues();
        $this->_processStaticAttributeValues();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _processStaticAttributeValues()
    {
        $connection = $this->getConnection();
        foreach ($this->staticAttributeValuesToSave as $table => $data) {
            foreach ($data as $datum) {
                $id = $datum[$this->getEntityIdField()];
                unset($datum[$this->getEntityIdField()]);
                $connection->update($table, $datum, [$this->getEntityIdField() . '=?' => $id]);
            }
        }

        $this->staticAttributeValuesToSave = [];

        return $this;
    }

    /**
     * @param DataObject $object
     * @param string     $attributeCode
     *
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(DataObject $object, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        if ($attribute->isStatic()) {
            $connection = $this->getConnection();
            $connection->beginTransaction();

            try {
                $newValue = $object->getData($attributeCode);
                if ($newValue === null || $newValue === false) {
                    $this->_deleteStaticAttribute($object, $attribute);
                } else {
                    $this->_updateStaticAttribute($object, $attribute, $newValue);
                }
                $this->_processAttributeValues();
                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }

            return $this;
        }

        return parent::saveAttribute($object, $attributeCode);
    }

    /**
     * @param AbstractModel|AbstractExtensibleModel $object
     * @param AbstractAttribute                     $attribute
     * @param mixed                                 $value
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        if ($attribute->isStatic()) {
            if ($value === null || $value === false) {
                $this->_deleteStaticAttribute($object, $attribute);
            } else {
                $this->_updateStaticAttribute($object, $attribute, $value);
            }
        } elseif ($attribute instanceof ScopedAttributeInterface) {
            $table = $attribute->getBackend()->getTable();
            if (!isset($this->_attributeValuesToSave[$table])) {
                $this->_attributeValuesToSave[$table] = [];
            }

            $entityIdField = $attribute->getBackend()->getEntityIdField();

            $storeId = $object->getStoreId();
            if ($attribute->isScopeWebsite()) {
                $storeIds = $this->_storeHelper->getStoreIdsByWebsiteId($storeId);
            } elseif ($attribute->isScopeStore()) {
                $storeIds = [$storeId];
            } else {
                $storeIds = [Store::DEFAULT_STORE_ID];
            }

            foreach ($storeIds as $storeId) {
                $data = [
                    $entityIdField                     => $object->getId(),
                    $attribute->getIdFieldName()       => $attribute->getId(),
                    ScopedAttributeInterface::STORE_ID => $storeId,
                    ScopedAttributeInterface::VALUE    => $this->_prepareValueForSave($value, $attribute)
                ];

                $this->_attributeValuesToSave[$table][] = $data;
            }
        } else {
            return parent::_saveAttribute($object, $attribute, $value);
        }

        return $this;
    }

    /**
     * Determine which fields that "use default value" was selected for
     *
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return $this
     */
    protected function prepareUseDefaults(AbstractModel $object)
    {
        if ($object->getStoreId()) {
            $useDefaults = $object->getData('use_default');
            if (!is_array($useDefaults)) {
                $useDefaults = [];
            }

            foreach ($object->getData() as $key => $value) {
                if ($value === false) {
                    $useDefaults[$key] = 1;
                }
            }

            $this->_helper->boolify($useDefaults);
            $this->_helper->removeFalse($useDefaults);

            if ($useDefaults) {
                $useDefaults = array_fill_keys(array_keys($useDefaults), false);

                $attributes = $this->getAttributesByCode();
                foreach ($useDefaults as $attributeCode => &$value) {
                    if (isset($attributes[$attributeCode])) {
                        /** @var ScopedAttributeInterface|AbstractAttribute $attribute */
                        $attribute = $attributes[$attributeCode];
                        if ($attribute->getBackendType() === 'int' || $attribute->getBackendType() === 'decimal') {
                            $value = null;
                        }
                    }
                }
            }

            $object->addData($useDefaults);
        }

        return $this;
    }

    /**
     * @param string $table
     *
     * @return bool|string
     */
    protected function getTablePrimaryIdField($table)
    {
        if (!isset($this->tablePrimaryFields[$table])) {
            $connection = $this->getConnection();

            $field = $connection->getAutoIncrementField($table);
            if (!$field) {
                $describe = $connection->describeTable($table);
                foreach ($describe as $description) {
                    if (isset($description['PRIMARY']) && (bool)$description['PRIMARY'] === true) {
                        $field = $description['COLUMN_NAME'];
                        break;
                    }
                }
            }

            $this->tablePrimaryFields[$table] = $field;
        }

        return $this->tablePrimaryFields[$table];
    }
}
