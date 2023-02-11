<?php

namespace Corra\Spreedly\Cron;

use Psr\Log\LoggerInterface;
use Corra\Spreedly\Gateway\Config\Config;
use Corra\Spreedly\Model\RemoveRedactedSavedCc as ModelRemoveRedactedSavedCc;

/**
 * Cron for Invoice Generation automatic
 */
class RemoveRedactedSavedCc
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ModelRemoveRedactedSavedCc
     */
    protected $modelRemoveRedactedSavedCc;

    /**
     * @param LoggerInterface $logger
     * @param ModelRemoveRedactedSavedCc $modelRemoveRedactedSavedCc
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        ModelRemoveRedactedSavedCc $modelRemoveRedactedSavedCc,
        Config $config
    ) {
        $this->logger = $logger;
        $this->modelRemoveRedactedSavedCc = $modelRemoveRedactedSavedCc;
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isRemoveCCRedactedCronEnabled()) {
            $this->logger->info('Remove redacted savedcc Cron is NOT enabled from admin configuration');
            return;
        }
        $this->modelRemoveRedactedSavedCc->execute();
    }
}
