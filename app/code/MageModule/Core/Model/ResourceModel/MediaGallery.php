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

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Api\Data\MediaGalleryInterface;
use MageModule\Core\Model\MediaGallery as MediaGalleryModel;
use MageModule\Core\Model\MediaGalleryConfigInterface;
use MageModule\Core\Model\MediaGalleryConfigPoolInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Io\FileFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class MediaGallery
 *
 * @package MageModule\Core\Model\ResourceModel
 */
class MediaGallery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var MediaGalleryConfigPoolInterface
     */
    private $configPool;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $mainTable;

    /**
     * @var string
     */
    private $valueTable;

    /**
     * MediaGallery constructor.
     *
     * @param Context                         $context
     * @param MediaGalleryConfigPoolInterface $configPool
     * @param FileFactory                     $ioFactory
     * @param Filesystem                      $filesystem
     * @param string|null                     $mainTable
     * @param string|null                     $valueTable
     * @param string|null                     $connectionName
     */
    public function __construct(
        Context $context,
        MediaGalleryConfigPoolInterface $configPool,
        FileFactory $ioFactory,
        Filesystem $filesystem,
        $mainTable,
        $valueTable,
        $connectionName = null
    ) {
        $this->mainTable  = $mainTable;
        $this->valueTable = $valueTable;
        parent::__construct($context, $connectionName);

        $this->configPool  = $configPool;
        $this->fileFactory = $ioFactory;
        $this->filesystem  = $filesystem;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->mainTable, MediaGalleryInterface::VALUE_ID);
    }

    /**
     * @param AbstractModel|MediaGalleryModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $attrCode = $this->getAttributeCodeById($object->getAttributeId());
        $config   = $this->configPool->getConfig($attrCode);
        $value    = $object->getData(MediaGalleryInterface::FILE);

        if ($object->isObjectNew() &&
            $value &&
            $object instanceof MediaGalleryModel &&
            $config instanceof MediaGalleryConfigInterface
        ) {
            $object->setOrigData(MediaGalleryInterface::FILE, $value);

            $path = $this->getMediaPath(true, $config->getMediaPath($value));

            /** if file already exists in destination dir, we make filename unique */
            $file  = Uploader::getNewFileName($path);
            $parts = explode(DIRECTORY_SEPARATOR, $value);
            array_pop($parts);
            $parts[] = $file;
            $value   = implode(DIRECTORY_SEPARATOR, $parts);
            $object->setFile($value);
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param string        $field
     * @param mixed         $value
     * @param AbstractModel $object
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $connection = $this->getConnection();
        $valueTable = $this->getValueTable();
        if ($connection->isTableExists($valueTable)) {
            $select->joinLeft(
                ['value_table' => $valueTable],
                $this->getMainTable() . '.' . $this->getIdFieldName() . ' = value_table.' . $this->getIdFieldName(),
                ['*']
            );

            $storeId = (int)$object->getData(MediaGalleryInterface::STORE_ID);
            if ($connection->tableColumnExists($valueTable, MediaGalleryInterface::STORE_ID)) {
                $select->where(
                    'value_table.' . MediaGalleryInterface::STORE_ID . ' = ' . $storeId
                );
            }
        }

        return $select;
    }

    /**
     * @param AbstractModel|MediaGalleryModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $parent    = parent::_afterSave($object);
        $attrCode  = $this->getAttributeCodeById($object->getAttributeId());
        $config    = $this->configPool->getConfig($attrCode);
        $value     = $object->getData(MediaGalleryInterface::FILE);
        $origValue = $object->getOrigData(MediaGalleryInterface::FILE);

        /** move tmp image file to final destination */
        if ($object->isObjectNew() &&
            $value &&
            $origValue &&
            $object instanceof MediaGalleryModel &&
            $config instanceof MediaGalleryConfigInterface
        ) {
            $src  = $config->getTmpMediaPath($origValue);
            $dest = $config->getMediaPath($value);

            /** @var File $io */
            $io = $this->fileFactory->create();
            $io->setAllowCreateFolders(true);
            $io->open(['path' => $this->getMediaPath(true)]);
            $io->mkdir($config->getMediaDir($value));
            $io->mv($src, $dest);
            $io->rm($src);
            $io->close();
        }

        $this->insertUpdateValue($object);

        return $parent;
    }

    /**
     * @return string
     */
    public function getValueTable()
    {
        if ($this->valueTable) {
            return $this->getTable($this->valueTable);
        }

        return null;
    }

    /**
     * @param AbstractModel $object
     *
     * @return array
     */
    private function getValueRow(AbstractModel $object)
    {
        $result = [];

        $table = $this->getValueTable();
        if ($table) {
            $connection = $this->getConnection();

            $select = $connection->select()->from($table);
            $select->where(MediaGalleryInterface::VALUE_ID . ' =?', $object->getData(MediaGalleryInterface::VALUE_ID));

            if ($connection->tableColumnExists($table, MediaGalleryModel::STORE_ID)) {
                $select->where(MediaGalleryInterface::STORE_ID . ' =?', $object->getData(MediaGalleryModel::STORE_ID));
            }

            $row = $connection->fetchRow($select);
            if ($row) {
                $result = [];
            }
        }

        return $result;
    }

    /**
     * @param AbstractModel $object
     *
     * @return bool
     */
    private function insertUpdateValue(AbstractModel $object)
    {
        $result = false;

        $table = $this->getValueTable();
        if ($table) {
            $connection = $this->getConnection();

            $row = $this->getValueRow($object);
            foreach ($object->getData() as $key => $value) {
                $row[$key] = $value;
            }

            $describe = $connection->describeTable($table);
            foreach ($row as $column => &$value) {
                if (!isset($describe[$column])) {
                    unset($row[$column]);
                } else {
                    $value = $connection->prepareColumnValue($describe[$column], $value);
                }
            }

            $result = (bool)$connection->insertOnDuplicate($table, $row);
        }

        return $result;
    }

    /**
     * @param AbstractModel|MediaGalleryModel $object
     *
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $attrCode = $this->getAttributeCodeById($object->getAttributeId());
        $config   = $this->configPool->getConfig($attrCode);
        $value    = $object->getData(MediaGalleryInterface::FILE);

        if ($value && $config instanceof MediaGalleryConfigInterface) {
            $filepath = $config->getMediaPath($value);

            /** @var File $io */
            $io = $this->fileFactory->create();
            $io->setAllowCreateFolders(true);
            $io->open(['path' => $this->getMediaPath(true)]);
            if ($io->fileExists($filepath, true)) {
                $io->rm($filepath);
            }
            $io->close();
        }

        return parent::_afterDelete($object);
    }

    /**
     * @param bool        $absolute
     * @param null|string $path
     *
     * @return string
     */
    private function getMediaPath($absolute = true, $path = null)
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if ($absolute) {
            return $dir->getAbsolutePath($path);
        }

        return $dir->getRelativePath($path);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    protected function getAttributeCodeById($id)
    {
        $connection = $this->getConnection();
        $select     = $connection->select();
        $select->from($this->getTable('eav_attribute'), 'attribute_code');
        $select->where('attribute_id = ?', $id);

        return $connection->fetchOne($select);
    }

    /**
     * @param int $entityId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllIdsByEntityId($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), MediaGalleryInterface::VALUE_ID);
        $select->where(MediaGalleryInterface::ENTITY_ID . '= ?', $entityId);

        return $connection->fetchCol($select);
    }
}
