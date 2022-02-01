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

namespace MageModule\Core\Model\ResourceModel\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Class ConditionBuilder
 *
 * @package MageModule\Core\Model\ResourceModel\Entity\Attribute
 */
class ConditionBuilder
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ConditionBuilder constructor.
     *
     * @param ResourceConnection    $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->connection   = $resourceConnection->getConnection();
        $this->storeManager = $storeManager;
    }

    /**
     * @param AbstractAttribute       $attribute
     * @param EntityMetadataInterface $metadata
     * @param array                   $scopes
     * @param int                     $linkFieldValue
     *
     * @return array
     * @throws LocalizedException
     */
    public function buildExistingAttributeWebsiteScope(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $website = $this->getWebsiteForWebsiteScope($scopes);
        if (!$website) {
            return [];
        }
        $storeIds = $website->getStoreIds();

        $condition = [
            $metadata->getLinkField() . ' = ?'    => $linkFieldValue,
            $attribute->getIdFieldName() . ' = ?' => $attribute->getAttributeId(),
        ];

        $conditions = [];
        foreach ($storeIds as $storeId) {
            $identifier                      = $this->connection->quoteIdentifier(Store::STORE_ID);
            $condition[$identifier . ' = ?'] = $storeId;
            $conditions[]                    = $condition;
        }

        return $conditions;
    }

    /**
     * Returns conditions for new attribute action (insert) if attribute scope is "website"
     *
     * @param AbstractAttribute       $attribute
     * @param EntityMetadataInterface $metadata
     * @param array                   $scopes
     * @param int                     $linkFieldValue
     *
     * @return array
     * @throws LocalizedException
     */
    public function buildNewAttributesWebsiteScope(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        $website = $this->getWebsiteForWebsiteScope($scopes);
        if (!$website) {
            return [];
        }
        $storeIds = $website->getStoreIds();

        $condition = [
            $metadata->getLinkField()    => $linkFieldValue,
            $attribute->getIdFieldName() => $attribute->getAttributeId(),
        ];

        $conditions = [];
        foreach ($storeIds as $storeId) {
            $condition[Store::STORE_ID] = $storeId;
            $conditions[]               = $condition;
        }

        return $conditions;
    }

    /**
     * @param array $scopes
     *
     * @return WebsiteInterface|null
     * @throws LocalizedException
     */
    private function getWebsiteForWebsiteScope(array $scopes)
    {
        $store = $this->getStoreFromScopes($scopes);
        if ($store instanceof StoreInterface) {
            return $this->storeManager->getWebsite($store->getId());
        }

        return null;
    }

    /**
     * @param array $scopes
     *
     * @return StoreInterface|null
     */
    private function getStoreFromScopes(array $scopes)
    {
        foreach ($scopes as $scope) {
            if (Store::STORE_ID === $scope->getIdentifier()) {
                return $this->storeManager->getStore($scope->getValue());
            }
        }

        return null;
    }
}
