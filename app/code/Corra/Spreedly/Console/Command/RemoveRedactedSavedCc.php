<?php

namespace Corra\Spreedly\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Corra\Spreedly\Model\RemoveRedactedSavedCc as ModelRemoveRedactedSavedCc;

class RemoveRedactedSavedCc extends Command
{
    /**
     * @var ModelRemoveRedactedSavedCc
     */
    protected $modelRemoveRedactedSavedCc;

    /**
     * @param ModelRemoveRedactedSavedCc $modelRemoveRedactedSavedCc
     * @param string|null $name
     */
    public function __construct(
        ModelRemoveRedactedSavedCc $modelRemoveRedactedSavedCc,
        string $name = null
    ) {
        $this->modelRemoveRedactedSavedCc = $modelRemoveRedactedSavedCc;
        parent::__construct($name);
    }

    /**
     * Configure this command
     */
    protected function configure()
    {
        $this->setName('remove_redacted_savedcc')
            ->setDescription('Remove the Redacted saved card from vault_payment_token table');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $output->writeln(__('Start removing the Redacted saved card')->getText());

        $this->modelRemoveRedactedSavedCc->execute();

        $output->writeln(__(sprintf('Completed removing the Redacted saved card in %s sec, please check the log file debug.log', round(microtime(true) - $start, 2)))->getText());
    }
}
