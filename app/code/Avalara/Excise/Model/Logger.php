<?php

namespace Avalara\Excise\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Magento\Framework\Exception\FileSystemException;

class Logger
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var array
     */
    protected $playback = [];

    /**
     * @var bool
     */
    protected $isRecording;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $console;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $filename = ExciseTaxConfig::TAX_DEFAULT_LOGGER;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var boolean
     */
    protected $isForced = false;

    /**
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ExciseTaxConfig $exciseTaxConfig
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ExciseTaxConfig $exciseTaxConfig
    ) {
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->exciseTaxConfig = $exciseTaxConfig;
    }

    /**
     * Sets the log filename
     *
     * @param string $filename
     * @return Logger
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Enables or disables the logger
     *
     * @param boolean $isForced
     * @return Logger
     */
    public function force($isForced = true)
    {
        $this->isForced = $isForced;
        return $this;
    }

    /**
     * Get the temp log filename
     *
     * @return string
     */
    public function getPath()
    {
        return $this->directoryList->getPath(DirectoryList::LOG)
            . DIRECTORY_SEPARATOR . 'excise' . DIRECTORY_SEPARATOR . $this->filename;
    }

    /**
     * Save a message to tax.log
     *
     * @param string $message
     * @param string $label
     * @throws LocalizedException
     * @return void
     */
    public function log($message, $label = '')
    {
        $throw = 0;
        try {
            if (!empty($label)) {
                $label = '[' . strtoupper($label) . '] ';
            }

            if (!$this->exciseTaxConfig->isProductionMode()) {
                $label = '[SANDBOX] ' . $label;
            }

            $timestamp = date('d M Y H:i:s', time());
            $message = sprintf('%s%s - %s%s', PHP_EOL, $timestamp, $label, $message);

            if (!$this->driverFile->isDirectory(
                $this->driverFile->getParentDirectory($this->getPath())
            )
            ) {
                // dir doesn't exist, create it
                $this->driverFile->createDirectory(
                    $this->driverFile->getParentDirectory($this->getPath()),
                    0775
                );
            }

            $this->driverFile->filePutContents($this->getPath(), $message, FILE_APPEND);

            if ($this->isRecording) {
                $this->playback[] = $message;
            }
            if ($this->console) {
                $this->console->write($message);
            }
            return  true;
        } catch (FileSystemException $exp) {
            $throw = 1;
        } catch (\Exception $e) {
            $throw = 1;
        }
        if ($throw) {
            // @codingStandardsIgnoreStart
            throw new LocalizedException(__('Could not write to your Magento log directory under /var/log. Please make sure the directory is created and check permissions for %1.', $this->directoryList->getPath('log')));
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * Enable log recording
     *
     * @return void
     */
    public function record()
    {
        $this->isRecording = true;
    }

    /**
     * Return log recording
     *
     * @return array
     */
    public function playback()
    {
        return $this->playback;
    }

    /**
     * Set console output interface
     *
     * @return void
     */
    public function console($output)
    {
        $this->console = $output;
    }
}
