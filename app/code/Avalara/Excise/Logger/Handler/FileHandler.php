<?php

namespace Avalara\Excise\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Avalara\Excise\Model\Config\Source\LoggingMode;
use Avalara\Excise\Helper\Config;
/**
 * @codeCoverageIgnore
 */
class FileHandler extends Base
{
    /**
     * File name without extension
     */
    const FILENAME = 'excise';

    /**
     * Location to store the file
     */
    const FILEPATH = 'var/log/excise/';

    /**
     * @var string
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var DriverInterface
     */
    protected $filesystem;

    /**
     * @var Config
     */
    protected $exciseConfig;

    /**
     * @param Config $config
     * @param DriverInterface $filesystem
     */
    public function __construct(
        Config $config,
        DriverInterface $filesystem
    ) {
        $this->exciseConfig = $config;
        $this->filesystem = $filesystem;

        parent::__construct(
            $filesystem,
            self::FILEPATH,
            $this->getFileName()
        );

        // Set our custom formatter so that the context and extra parts of the record will print on multiple lines
        $this->setFormatter(new LineFormatter());
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
    }

    public function getFileName()
    {
        $date = $this->exciseConfig->getTimeZoneObject()->date();
        $date = $date->format('d-m-y');
        return self::FILENAME . '-' . $date . ".log";
    }

    /**
     * Checking config a value, and conditionally adding extra processors to the handler
     *
     * @param array $processors
     */
    protected function addExtraProcessors(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * Uses the admin configuration settings to determine if the record should be handled
     *
     * @param array $record
     * @return Boolean
     */
    public function isHandling(array $record) : bool
    {
        return $this->exciseConfig->isModuleEnabled()
            && $this->exciseConfig->getLogEnabled()
            && ($this->exciseConfig->getLogMode() == LoggingMode::LOGGING_MODE_FILE);
    }

    /**
     * Writes the log to the filesystem
     *
     * @param $record array
     * @return void
     */
    public function write(array $record) : void
    {
        // Custom parsing can be added here

        parent::write($record);
    }
}
