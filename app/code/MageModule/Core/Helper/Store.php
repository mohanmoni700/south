<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Store
 *
 * @package MageModule\Core\Helper
 */
class Store extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $storeIdsByWebsiteId = [];

    /**
     * Store constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Context               $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
    }

    /**
     * Allows to get store ids by website ID without breaking service contract
     *
     * @param int|string $websiteId
     *
     * @return array
     */
    public function getStoreIdsByWebsiteId($websiteId)
    {
        if (!isset($this->storeIdsByWebsiteId[$websiteId])) {
            $this->storeIdsByWebsiteId[$websiteId] = [];

            $stores = $this->storeManager->getStores(false);

            /** @var StoreInterface $store */
            foreach ($stores as $store) {
                if ((int)$store->getWebsiteId() === (int)$websiteId) {
                    $this->storeIdsByWebsiteId[$websiteId][] = $store->getId();
                }
            }
        }

        return $this->storeIdsByWebsiteId[$websiteId];
    }
}
