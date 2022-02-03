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
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Model\ResourceModel;

use MageModule\Core\Helper\File as Helper;
use MageModule\Core\Api\Data\MediaGalleryInterface;
use MageModule\Core\Model\MediaGalleryConfigPoolInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Io\FileFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class MediaGalleryImport
 *
 * @package MageModule\Core\Model\ResourceModel
 */
class MediaGalleryImport extends MediaGallery
{
    /**
     * @var Helper
     */
    private $helper;

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
     * MediaGalleryImport constructor.
     *
     * @param Helper                          $helper
     * @param Context                         $context
     * @param MediaGalleryConfigPoolInterface $configPool
     * @param FileFactory                     $ioFactory
     * @param Filesystem                      $filesystem
     * @param string                          $mainTable
     * @param string                          $valueTable
     * @param string|null                     $connectionName
     */
    public function __construct(
        Helper $helper,
        Context $context,
        MediaGalleryConfigPoolInterface $configPool,
        FileFactory $ioFactory,
        Filesystem $filesystem,
        string $mainTable,
        string $valueTable,
        string $connectionName = null
    ) {
        parent::__construct(
            $context,
            $configPool,
            $ioFactory,
            $filesystem,
            $mainTable,
            $valueTable,
            $connectionName
        );

        $this->helper      = $helper;
        $this->configPool  = $configPool;
        $this->fileFactory = $ioFactory;
        $this->filesystem  = $filesystem;
    }

    /**
     * Since importing from CSV, move file from the declared upload location to the
     * MediaGallery tmp media directory so that we can pass it through the usual
     * image upload flow
     *
     * @param AbstractModel $object
     *
     * @return \MageModule\Core\Model\ResourceModel\MediaGallery
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $relFilepath = $object->getData(MediaGalleryInterface::FILE);
        if ($relFilepath) {
            $rootDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
            $src     = $rootDir->getAbsolutePath($relFilepath);
            if ($this->helper->fileExists($src)) {
                $config = $this->configPool->getConfig(
                    $this->getAttributeCodeById($object->getData(MediaGalleryInterface::ATTRIBUTE_ID))
                );

                $filename = $this->helper->getBasename($relFilepath);

                if (method_exists(Uploader::class, 'getDispersionPath')) {
                    $dispersionPath = Uploader::getDispersionPath($filename);
                } elseif (method_exists(Uploader::class, 'getDispretionPath')) {
                    $dispersionPath = Uploader::getDispretionPath($filename);
                } else {
                    // should never reach here but don't want a potentially undefined variable
                    $dispersionPath = $filename;
                }

                $mediaDir    = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $tmpMediaDir = $mediaDir->getAbsolutePath($config->getTmpMediaPath($dispersionPath));
                $filename    = Uploader::getNewFileName($tmpMediaDir . DIRECTORY_SEPARATOR . $filename);
                $dest        = $tmpMediaDir . DIRECTORY_SEPARATOR . $filename;

                /** @var File $io */
                $io = $this->fileFactory->create();
                $io->setAllowCreateFolders(true);
                $io->open(['path' => $this->helper->getDirname($src)]);
                $io->mkdir($this->helper->getDirname($dest), 0777);
                $io->cp($src, $dest);
                $io->close();

                $parts         = explode(DIRECTORY_SEPARATOR, $dest);
                $filteredParts = array_slice($parts, -3, 3, true);
                $value         = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $filteredParts);
                $object->setData(MediaGalleryInterface::FILE, $value);
            }
        }

        return parent::_beforeSave($object);
    }
}
