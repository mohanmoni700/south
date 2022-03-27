<?php
namespace Alfakher\CompanyImport\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * New name
     *
     * @var string
     */
    protected $fileName = '/var/log/import_script/company_import.log';
}
