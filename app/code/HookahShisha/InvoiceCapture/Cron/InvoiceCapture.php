<?php

namespace HookahShisha\InvoiceCapture\Cron;

use HookahShisha\InvoiceCapture\Model\Config;
use HookahShisha\InvoiceCapture\Model\InvoiceCaptureProcessor;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Cron for Invoice Generation automatic
 */
class InvoiceCapture
{
    /**
     * @var InvoiceCaptureProcessor
     */
    private $invoiceCaptureProcessor;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InvoiceCaptureProcessor constructor.
     *
     * @param InvoiceCaptureProcessor $invoiceCaptureProcessor
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        InvoiceCaptureProcessor $invoiceCaptureProcessor,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->invoiceCaptureProcessor = $invoiceCaptureProcessor;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * InvoiceCaptureProcessor Processor (cron process)
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            $this->logger->error('INVOICE_CAPTURE_AUTOMATIC NOT ENABLED');
            return;
        }
        if (!$this->config->isCronEnabled()) {
            $this->logger->error('INVOICE_CAPTURE_AUTOMATIC CRON NOT ENABLED');
            return;
        }
        try {
            $this->invoiceCaptureProcessor->execute();
        } catch (Exception $e) {
            $this->logger->error('INVOICE_CAPTURE_AUTOMATIC CRON :: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }
}
