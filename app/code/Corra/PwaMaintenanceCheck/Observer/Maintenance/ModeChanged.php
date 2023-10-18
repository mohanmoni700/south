<?php
declare(strict_types=1);

namespace Corra\PwaMaintenanceCheck\Observer\Maintenance;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class ModeChanged implements ObserverInterface
{
    /**
     * Maintenance PWA flag file name in pub/media
     */
    private const FLAG_PWAFILENAME = 'healthy.jpg';
    /**
     * PWA Maintenance flag dir
     */
    private const FLAG_MEDIADIR = DirectoryList::MEDIA;

    /**
     * Path to store files
     * @var WriteInterface
     */
    protected $pwaflagDir;

    /**
     * @param Filesystem $filesystem
     * @throws FileSystemException
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->pwaflagDir = $filesystem->getDirectoryWrite(self::FLAG_MEDIADIR);
    }

    /**
     * Execute Method
     *
     * @param Observer $observer
     * @return bool|void
     * @throws FileSystemException
     */
    public function execute(
        Observer $observer
    ) {
        $isOn = $observer->getData('isOn');
        if ($isOn) {
            if ($this->pwaflagDir->isExist(self::FLAG_PWAFILENAME)) {
                return $this->pwaflagDir->delete(self::FLAG_PWAFILENAME);
            }
        }
        return $this->pwaflagDir->touch(self::FLAG_PWAFILENAME);
    }
}
