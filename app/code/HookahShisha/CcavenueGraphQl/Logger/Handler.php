<?php
namespace HookahShisha\CcavenueGraphQl\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{

    /**
     * Logging level
     *
     * @var integer
     */
    protected $loggerType = Logger::INFO;

    /**
     * ccavenuegraphql
     *
     * @var string
     */
    protected $fileName = '/var/log/ccavenuegraphql.log';
}//end class
