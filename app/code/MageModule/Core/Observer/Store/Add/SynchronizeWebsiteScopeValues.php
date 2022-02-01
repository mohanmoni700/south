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

namespace MageModule\Core\Observer\Store\Add;

use MageModule\Core\Api\AttributeRepositoryInterface;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use MageModule\Core\Model\ResourceModel\Entity\ScopedAttribute\WebsiteValuesSynchronizer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface as MageScopedAttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

/**
 * Class SynchronizeWebsiteScopeValues
 *
 * @package MageModule\Core\Observer\Store\Add
 */
class SynchronizeWebsiteScopeValues implements ObserverInterface
{
    /**
     * @var WebsiteValuesSynchronizer
     */
    private $synchronizer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * InsertWebsiteScopeValues constructor.
     *
     * @param WebsiteValuesSynchronizer    $synchronizer
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        WebsiteValuesSynchronizer $synchronizer,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->synchronizer          = $synchronizer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository   = $attributeRepository;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        if ($store instanceof StoreInterface) {
            if ($store->getId() > Store::DEFAULT_STORE_ID) {
                $this->searchCriteriaBuilder->addFilter(
                    ScopedAttributeInterface::IS_GLOBAL,
                    MageScopedAttributeInterface::SCOPE_WEBSITE
                );

                $list = $this->attributeRepository->getList($this->searchCriteriaBuilder->create());

                /** @var ScopedAttributeInterface $attribute */
                foreach ($list->getItems() as $attribute) {
                    $this->synchronizer->synchronize($attribute, $store->getWebsiteId());
                }
            }
        }
    }
}
