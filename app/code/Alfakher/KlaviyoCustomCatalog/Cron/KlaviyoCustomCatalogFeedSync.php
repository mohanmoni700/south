<?php
declare (strict_types = 1);

namespace Alfakher\KlaviyoCustomCatalog\Cron;

class KlaviyoCustomCatalogFeedSync
{
    /**
     * KlaviyoCustomCatalogFeedSync constructor
     *
     * @param \Alfakher\KlaviyoCustomCatalog\Model\KlaviyoCustomCatalog $klaviyoCustomCatalogModel
     */
    public function __construct(
        \Alfakher\KlaviyoCustomCatalog\Model\KlaviyoCustomCatalog $klaviyoCustomCatalogModel
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
