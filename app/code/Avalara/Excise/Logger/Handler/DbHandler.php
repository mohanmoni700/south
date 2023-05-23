<?php

namespace Avalara\Excise\Logger\Handler;

use Avalara\Excise\Model\Config\Source\LogDetail;
use Avalara\Excise\Model\Config\Source\LoggingMode;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Avalara\Excise\Helper\Config;
use Avalara\Excise\Model\LogFactory;
/**
 * @codeCoverageIgnore
 */
class DbHandler extends AbstractHandler
{
    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Config
     */
    protected $exciseConfig;

    public function __construct(
        Config $config,
        LogFactory $logFactory
    ) {
        $this->logFactory = $logFactory;
        $this->exciseConfig = $config;
        parent::__construct(Logger::DEBUG, true);
        $introspectionProcessor = new IntrospectionProcessor();
        $webProcessor = new WebProcessor();
        $this->addExtraProcessors([$introspectionProcessor, $webProcessor]);
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
        return $this->exciseConfig->isModuleEnabled() && $this->exciseConfig->getLogEnabled() && ($this->exciseConfig->getLogMode() == LoggingMode::LOGGING_MODE_DB);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record) : bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);
        $record['formatted'] = $record;
        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Writes the log to the database by utilizing the Log model
     *
     * @param $record array
     * @return void
     */
    public function write(array $record)
    {
        # Log to database
        /** @var \Avalara\Excise\Model\Log $log */
        $log = $this->logFactory->create();

        $log->setData('level', isset($record['level_name']) ? $record['level_name'] : null);
        $log->setData('message', isset($record['message']) ? $record['message'] : null);

        if (isset($record['extra']['store_id'])) {
            $log->setData('store_id', $record['extra']['store_id']);
            unset($record['extra']['store_id']);
        }
        if (isset($record['context']['extra']['class'])) {
            $log->setData('source', $record['context']['extra']['class']);
        } elseif (isset($record['extra']['class']) && isset($record['extra']['line'])) {
            $log->setData('source', $record['extra']['class'] . " [line:" . $record['extra']['line'] . "]");
        }

        $log->setData('request', $this->getRequest($record));
        $log->setData('result', $this->getResult($record));
        $log->setData(
            'additional',
            $this->getExtraVarExport($record)
        );
        $log->save();
    }

    /**
     * If the record contains a context key
     * export the variable contents and return it
     *
     * @param array $record
     * @return string
     */
    protected function getContextVarExport(array $record)
    {
        $string = "";
        if (isset($record['context']) && count($record['context']) > 0) {
            $string = 'context: ' . var_export($record['context'], true);
        }
        return $string;
    }

    /**
     * If the record contains a extra key in the context
     * export the variable contents, return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getExtraVarExport(array $record)
    {
        $string = "";
        if (isset($record['extra']) && count($record['extra']) > 0) {
            $string = 'extra: ' . var_export($record['extra'], true);
        }
        return $string;
    }

    /**
     * If the record contains a request key in the context
     * return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getRequest(array &$record)
    {
        $string = "";
        if (isset($record['context']['request'])) {
            $string = $record['context']['request'];
            unset($record['context']['request']);
        }
        return $string;
    }

    /**
     * If the record contains a result key in the context
     * return it, and remove the element from the context array
     *
     * @param array $record
     * @return string
     */
    protected function getResult(array &$record)
    {
        $string = "";
        if (isset($record['context']['result'])) {
            $string = $record['context']['result'];
            unset($record['context']['result']);
        }
        return $string;
    }

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }
        return $record;
    }
}
