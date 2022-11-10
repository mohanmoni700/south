<?php
declare(strict_types=1);

namespace Alfakher\KlaviyoCustomCatalog\Console;

use Alfakher\KlaviyoCustomCatalog\Model\KlaviyoCustomCatalog;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates latest feed for klaviyo custom catalog sync
 */
class KlaviyoCustomCatalogFeedGenerate extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var KlaviyoCustomCatalog
     */
    protected $klaviyoCustomCatalogModel;

    /**
     * KlaviyoCustomCatalogFeedGenerate constructor
     *
     * @param State $state
     * @param KlaviyoCustomCatalog $klaviyoCustomCatalogModel
     */
    public function __construct(
        State $state,
        KlaviyoCustomCatalog $klaviyoCustomCatalogModel
    ) {
        parent::__construct();
        $this->state = $state;
        $this->klaviyoCustomCatalogModel = $klaviyoCustomCatalogModel;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('klaviyo:customcatalog:sync');
        $this->setDescription('Generates & Syncs Klaviyo Custom Catalog Feed');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $output->writeln("<info>Generating feed, Please wait...</info>");
        try {
            $this->klaviyoCustomCatalogModel->generateKlaviyoCustomCatalogFeed();
            $output->writeln("<info>Feed generated successfully</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Some error occured while Generating the feed</error>");
        }
    }
}
