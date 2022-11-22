<?php
declare(strict_types=1);

namespace Alfakher\KlaviyoCustomCatalog\Cron;

use Alfakher\KlaviyoCustomCatalog\Model\KlaviyoCustomCatalog;

class KlaviyoCustomCatalogFeedSync
{
    /**
     * @var KlaviyoCustomCatalog
     */
    protected $klaviyoCustomCatalogModel;
    
    /**
     * KlaviyoCustomCatalogFeedSync constructor
     *
     * @param KlaviyoCustomCatalog $klaviyoCustomCatalogModel
     */
    public function __construct(
        KlaviyoCustomCatalog $klaviyoCustomCatalogModel
    ) {
        $this->klaviyoCustomCatalogModel = $klaviyoCustomCatalogModel;
    }

    /**
     * Generates latest klaviyo custom catalog feed for sync
     *
     * @return void
     */
    public function execute()
    {
        $this->klaviyoCustomCatalogModel->generateKlaviyoCustomCatalogFeed();
    }
}
