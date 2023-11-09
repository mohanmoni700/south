<?php

declare(strict_types=1);

namespace Ooka\Catalog\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;
    /**
     * Logger File name
     *
     * @var string
     */
    protected $fileName = '/var/log/ooka-thankyou-email.log';
}
