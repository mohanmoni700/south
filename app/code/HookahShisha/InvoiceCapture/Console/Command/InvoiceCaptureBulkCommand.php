<?php
declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Console\Command;

use HookahShisha\InvoiceCapture\Model\Config;
use HookahShisha\InvoiceCapture\Model\InvoiceCaptureProcessor;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;

/**
 * Command to cpature invoice via Bulk operation
 */
class InvoiceCaptureBulkCommand extends Command
{
    /**
     * @var InvoiceCaptureProcessor
     */
    private $invoiceCaptureProcessor;
    /**
     * @var Config
     */
    private $config;

    /** @var State **/
    private $state;

    /**
     * Invoice Capture bulk Command constructor.
     *
     * @param InvoiceCaptureProcessor $invoiceCaptureProcessor
     * @param Config $config
     * @param State $state
     * @param string|null $name
     */
    public function __construct(
        InvoiceCaptureProcessor $invoiceCaptureProcessor,
        Config $config,
        State $state,
        string $name = null
    ) {
        parent::__construct($name);
        $this->invoiceCaptureProcessor = $invoiceCaptureProcessor;
        $this->config = $config;
        $this->state = $state;
    }

    /**
     * Invoice Capture change command
     */
    protected function configure()
    {
        $this->setName('hookahshisha:invoice-capture:automatic')
            ->setDescription('Create the Invoice and Capture the Payment Automatically after Shipped Status');
        parent::configure();
    }

    /**
     * Invoice Capture change process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        } catch (LocalizedException $e) {
            $output->writeln(
                'Could not Create the Invoice. <error>' . $e->getMessage() . '</error>',
                OutputInterface::OUTPUT_NORMAL
            );
        }

        if ($this->config->isEnabled() !== true) {
            $output->writeln(
                '<error>Automatic Invoice Capture feature is not enabled</error>',
                OutputInterface::OUTPUT_NORMAL
            );
            return;
        }

        try {
            $this->invoiceCaptureProcessor->execute();
        } catch (Exception $e) {
            $output->writeln(
                'Could not Create the Invoice. <error>' . $e->getMessage() . '</error>',
                OutputInterface::OUTPUT_NORMAL
            );
        }
    }
}
