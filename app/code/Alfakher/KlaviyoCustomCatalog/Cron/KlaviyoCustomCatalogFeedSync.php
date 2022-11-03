<?php
declare (strict_types = 1);

namespace Alfakher\KlaviyoCustomCatalog\Cron;

use Alfakher\KlaviyoCustomCatalog\Model\KlaviyoCustomCatalog;

class KlaviyoCustomCatalogFeedSync
{
    /**
     * KlaviyoCustomCatalogFeedSync constructor
     *
     * @param KlaviyoCustomCatalog $klaviyoCustomCatalogModel [description]
     */
    public function __construct(
        KlaviyoCustomCatalog $klaviyoCustomCatalogModel
    ) {
        $this->_klaviyoCustomCatalogModel = $klaviyoCustomCatalogModel;
    }

    /**
     * Generates latest klaviyo custom catalog feed for sync
     *
     * @return void
     */
    public function execute()
    {
        $this->_klaviyoCustomCatalogModel->generateKlaviyoCustomCatalogFeed();
    }
}
