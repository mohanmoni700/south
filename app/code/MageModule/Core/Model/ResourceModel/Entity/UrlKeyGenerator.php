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

namespace MageModule\Core\Model\ResourceModel\Entity;

use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class UrlKeyGenerator
 *
 * @package MageModule\Core\Model\ResourceModel\Entity
 */
class UrlKeyGenerator
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $defaultSuffix;

    /**
     * @var string|null
     */
    private $xmlPathSuffix;

    /**
     * @var array
     */
    private $suffixes = [];

    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * UrlKeyGenerator constructor.
     *
     * @param \Magento\UrlRewrite\Model\StorageInterface         $storage
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param null|string                                        $defaultSuffix
     * @param null|string                                        $xmlPathSuffix
     */
    public function __construct(
        StorageInterface $storage,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        $defaultSuffix = null,
        $xmlPathSuffix = null
    ) {
        $this->storage       = $storage;
        $this->storeManager  = $storeManager;
        $this->scopeConfig   = $scopeConfig;
        $this->defaultSuffix = $defaultSuffix;
        $this->xmlPathSuffix = $xmlPathSuffix;
    }

    /**
     * @param AbstractAttribute $attribute
     *
     * @return $this
     */
    public function setAttribute(AbstractAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return $this
     */
    public function setDefaultSuffix($suffix)
    {
        $this->defaultSuffix = $suffix;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setSuffixXmlConfigPath($path)
    {
        $this->xmlPathSuffix = $path;

        return $this;
    }

    /**
     * @param int|null $storeId
     *
     * @return string|null
     */
    private function getSuffix($storeId = null)
    {
        if ($this->xmlPathSuffix && is_numeric($storeId)) {
            if (!isset($this->suffixes[$storeId])) {
                $this->suffixes[$storeId] = $this->scopeConfig->getValue(
                    $this->xmlPathSuffix,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }

            return $this->suffixes[$storeId];
        }

        return $this->defaultSuffix;
    }

    /**
     * @param AbstractModel $object
     * @param string        $value
     * @param int[]         $storeIds
     *
     * @return array
     */
    private function getProjectedUrlKeys(AbstractModel $object, $value, array $storeIds)
    {
        $objectId   = $object->getId();
        $entityType = $this->attribute->getEntityType()->getEntityTypeCode();

        $urlKeys = [];
        foreach ($storeIds as $storeId) {
            $suffix     = $this->getSuffix($storeId);
            $rawValue   = preg_replace('#' . preg_quote($suffix) . '$#i', '', $value, 1);
            $finalValue = $rawValue . $suffix;

            $urlKeyData = [
                UrlRewrite::STORE_ID     => $storeId,
                UrlRewrite::ENTITY_ID    => $objectId,
                UrlRewrite::ENTITY_TYPE  => $entityType,
                UrlRewrite::REQUEST_PATH => $finalValue
            ];

            $urlKeys[] = array_filter($urlKeyData, 'strlen');
        }

        return $urlKeys;
    }

    /**
     * @param array $urlKeyData
     *
     * @return bool
     */
    private function checkUrlKeyAvailability(array $urlKeyData)
    {
        $excludeValueIds = [];
        $requestPaths    = [];
        $storeIds        = [];
        foreach ($urlKeyData as &$urlKeyDatum) {
            $rewrite = $this->storage->findOneByData($urlKeyDatum);
            if ($rewrite instanceof UrlRewrite) {
                $excludeValueIds[] = $rewrite->getUrlRewriteId();
            }

            $requestPaths[] = $urlKeyDatum[UrlRewrite::REQUEST_PATH];
            $storeIds[]     = $urlKeyDatum[UrlRewrite::STORE_ID];
        }

        $excludeValueIds = array_unique($excludeValueIds);
        $requestPaths    = array_unique($requestPaths);
        $storeIds        = array_unique($storeIds);

        $rewrites = $this->storage->findAllByData(
            [
                UrlRewrite::REQUEST_PATH => $requestPaths,
                UrlRewrite::STORE_ID     => $storeIds
            ]
        );

        /** @var UrlRewrite $rewrite */
        foreach ($rewrites as $key => $rewrite) {
            if (in_array($rewrite->getUrlRewriteId(), $excludeValueIds)) {
                unset($rewrites[$key]);
            }
        }

        return empty($rewrites);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function makeUnique($string)
    {
        return $string . '-' . substr(uniqid(rand(), true), 0, 4);
    }

    /**
     * Generates URL key for the given object based on the attribute passed in.
     * Attribute scope is taken into account. If the url key is not available,
     * will return the url key plus a string will be appended to the end of the
     * url key to make it unique
     *
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return string
     */
    public function generate(AbstractModel $object)
    {
        $attrCode = $this->attribute->getAttributeCode();
        $value    = $object->getData($attrCode);

        $storeIdField = 'store_id';
        if ($object instanceof AbstractExtensibleModel) {
            $storeIdField = AbstractExtensibleModel::STORE_ID;
        }

        if ($this->attribute instanceof ScopedAttributeInterface) {
            $storeId = $object->getData($storeIdField);

            $storeIds = [$storeId];

            /**
             * check to make sure desired url key is available in the specified scope.
             * if url key is not available, append some sort of uniqueness to it
             */
            if ($this->attribute->isScopeWebsite() && $storeId) {
                /** if website scope and not saving to default scope */
                $storeIds = $this->storeManager
                    ->getStore($storeId)
                    ->getWebsite()
                    ->getStoreIds();
            } elseif ($this->attribute->isScopeStore() && $storeId) {
                /** if store scope and not saving to default store */
                $storeIds = [$storeId];
            } elseif ($this->attribute->isScopeGlobal()) {
                /** if global scope, need to check availablility for all store ids */
                $storeIds = array_keys($this->storeManager->getStores(false));
            }
        } else {
            //TODO test further when using a non-scoped object/attribute. Currently, we aren't using any
            /** if not scoped attribute, then it's global */
            $storeIds = array_keys($this->storeManager->getStores(false));
        }

        $urlKeys = $this->getProjectedUrlKeys($object, $value, $storeIds);

        $i = 1;
        while (!$this->checkUrlKeyAvailability($urlKeys) && $i <= 100) {
            $newValue = $this->makeUnique($value);
            $urlKeys  = $this->getProjectedUrlKeys($object, $newValue, $storeIds);
            $value    = $newValue;
            $i++;
        }

        return $value;
    }
}
