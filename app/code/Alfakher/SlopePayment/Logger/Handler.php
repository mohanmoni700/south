<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    public const LOGGER_FILE_NAME = "/var/log/slope_debug.log";
   
   /**
    * Logging level
    * @var int
    */
    protected $loggerType = Logger::DEBUG;

   /**
    * File name for store logs
    * @var string
    */
    protected $fileName = self::LOGGER_FILE_NAME;
}
