<?php
/**
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
 */

namespace MageModule\Core\Model\ResourceModel\MediaGallery;

use MageModule\Core\Api\Data\MediaGalleryInterface;
use Magento\Store\Model\Store;

/**
 * Class Collection
 *
 * @package MageModule\Core\Model\ResourceModel\MediaGallery
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \MageModule\Core\Model\ResourceModel\MediaGallery
     */
    protected $_resource;

    /**
     * @var int
     */
    private $storeId = Store::DEFAULT_STORE_ID;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \MageModule\Core\Model\MediaGallery::class,
            \MageModule\Core\Model\ResourceModel\MediaGallery::class
        );
    }

    /**
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Zend_Db_Select_Exception
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $connection = $this->getConnection();
        $valueTable = $this->_resource->getValueTable();
        if ($valueTable) {
            $select = $this->getSelect();

            $storeIdCol = MediaGalleryInterface::STORE_ID;
            $valueIdCol = MediaGalleryInterface::VALUE_ID;

            $valueColumns    = array_keys($connection->describeTable($valueTable));
            $excludedColumns = [
                MediaGalleryInterface::VALUE_ID,
                MediaGalleryInterface::ENTITY_ID,
                MediaGalleryInterface::STORE_ID
            ];

            if ($connection->tableColumnExists($valueTable, $storeIdCol)) {
                foreach ($excludedColumns as $excludedColumn) {
                    $key = array_search($excludedColumn, $valueColumns);
                    if ($key !== false) {
                        unset($valueColumns[$key]);
                    }
                }

                $selects = [];

                $defaultStoreSelect = $connection->select()->from(['default_store_table' => $valueTable]);
                $defaultStoreSelect->where("default_store_table.{$storeIdCol} =?", Store::DEFAULT_STORE_ID);
                $selects[] = $defaultStoreSelect;

                if ($this->storeId) {
                    $storeSelect = $connection->select()->from(['store_table' => $valueTable]);
                    $storeSelect->where("store_table.{$storeIdCol} =?", $this->storeId);
                    $selects[] = $storeSelect;
                }

                if (count($selects) > 1) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $union = $connection->select()->union($selects);
                    $union->order($storeIdCol . ' ' . self::SORT_ORDER_DESC);

                    $storeSelect = $connection->select()->from($union);
                    $storeSelect->group($valueIdCol);
                } else {
                    $storeSelect = $defaultStoreSelect;
                }

                $select->joinLeft(
                    ['value_table' => $storeSelect],
                    "main_table.{$valueIdCol} = value_table.{$valueIdCol}",
                    $valueColumns
                );

                $select->columns(new \Zend_Db_Expr("'{$this->storeId}' AS {$storeIdCol}"));
            } else {
                $select->joinLeft(
                    ['value_table' => $valueTable],
                    "main_table.{$valueIdCol} = value_table.{$valueIdCol}",
                    $valueColumns
                );
            }
        }

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->storeId = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if (isset($condition[0]['eq']) && isset($field[0]) && $field[0] === MediaGalleryInterface::STORE_ID) {
            $this->setStoreId($condition[0]['eq']);
        } else {
            parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }
}
