<?php
/**
 * @author C O R R A
 */
declare(strict_types=1);

namespace HookahShisha\PWAmaintanenceCheck\Observer\Maintenance;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ModeChanged implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Maintenance PWA flag file name in pub/media
     */
    public const FLAG_PWAFILENAME = 'healthy.jpg';
    /**
     * PWA Maintenance flag dir
     */
    public const FLAG_MEDIADIR = DirectoryList::MEDIA;

    /**
     * Path to store files
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $pwaflagDir;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->pwaflagDir = $filesystem->getDirectoryWrite(self::FLAG_MEDIADIR);
    }

    /**
     * Execute observer
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */

    /**
     * Adding check to see if maintenance mode is enabled
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $isOn = $observer->getData('isOn');
        if ($isOn && $this->pwaflagDir->isExist(self::FLAG_PWAFILENAME)) {
            return $this->pwaflagDir->delete(self::FLAG_PWAFILENAME);
        }
        return $this->pwaflagDir->touch(self::FLAG_PWAFILENAME);
    }
}
