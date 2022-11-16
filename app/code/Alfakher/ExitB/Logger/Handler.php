<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Logger;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Handeler Log
 */
class Handler extends BaseHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * File
     *
     * @var string
     */
    protected $fileName = '/var/log/cancelOrder.log';
}
