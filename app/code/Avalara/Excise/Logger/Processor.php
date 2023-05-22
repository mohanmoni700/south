<?php

namespace Avalara\Excise\Logger;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Injects additional data
 * @codeCoverageIgnore
 */
class Processor
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieves the current Store ID from Magento and adds it to the record
     *
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // get the store_id and add it to the record
        $store = $this->storeManager->getStore();
        $record['extra']['store_id'] = $store->getId();

        return $record;
    }
}
