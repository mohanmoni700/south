<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Helper;

use Psr\Log\LoggerInterface;

class Logger
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function log($message)
    {
        $this->logger->info($message);
    }
}
