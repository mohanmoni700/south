<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Observer\Store\Add;

use MageModule\Core\Api\AttributeRepositoryInterface;
use MageModule\Core\Model\ResourceModel\AbstractEntity;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

class InsertWebsiteScopeValues implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var AbstractEntity
     */
    private $resource;

    /**
     * @var SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * InsertWebsiteScopeValues constructor.
     *
     * @param AbstractEntity                 $resource
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param AttributeRepositoryInterface   $attributeRepository
     */
    public function __construct(
        AbstractEntity $resource,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->resource              = $resource;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->attributeRepository   = $attributeRepository;
    }

    /**
     * After new store view is added, this observer inserts attribute value row
     * for any attributes that have website scope
     *
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $storeId = $observer->getEvent()->getStore()->getStoreId();
        if ($storeId) {
            /** @var SearchCriteriaInterface $searchCriteria */
            $searchCriteria = $this->searchCriteriaFactory->create();
            $attributes     = $this->attributeRepository->getList($searchCriteria);

            foreach ($attributes->getItems() as $attribute) {
                $this->resource->fillWebsiteValuesForAttribute(
                    $attribute->getAttributeCode(),
                    null,
                    $storeId
                );
            }
        }
    }
}
