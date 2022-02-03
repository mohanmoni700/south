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
use MageModule\Core\Api\Data\AttributeInterface;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

/**
 * Class UrlRewriteGenerator
 *
 * @package MageModule\Core\Model\ResourceModel\Entity
 */
class UrlRewriteGenerator
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
    protected $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * @var string|null
     */
    private $defaultSuffix;

    /**
     * @var string|null
     */
    private $xmlPathSuffix;

    /**
     * @var string|null
     */
    private $xmlPathCreateRedirect;

    /**
     * @var string|null
     */
    private $targetPathBase;

    /**
     * @var string|null
     */
    private $targetPathIdKey;

    /**
     * @var bool
     */
    protected $createRedirectDefaultValue = true;

    /**
     * @var array
     */
    private $createRedirectConfig = [];

    /**
     * @var array
     */
    private $suffixes = [];

    /**
     * UrlRewriteGenerator constructor.
     *
     * @param \Magento\UrlRewrite\Model\StorageInterface            $storage
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection             $resource
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param string|null                                           $defaultSuffix
     * @param string|null                                           $xmlPathSuffix
     * @param string|null                                           $xmlPathCreateRedirect
     * @param string|null                                           $targetPathBase
     * @param string|null                                           $targetPathIdKey
     */
    public function __construct(
        StorageInterface $storage,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource,
        UrlRewriteFactory $urlRewriteFactory,
        $defaultSuffix = null,
        $xmlPathSuffix = null,
        $xmlPathCreateRedirect = null,
        $targetPathBase = null,
        $targetPathIdKey = null
    ) {
        $this->storage               = $storage;
        $this->storeManager          = $storeManager;
        $this->scopeConfig           = $scopeConfig;
        $this->resource              = $resource;
        $this->urlRewriteFactory     = $urlRewriteFactory;
        $this->defaultSuffix         = $defaultSuffix;
        $this->xmlPathSuffix         = $xmlPathSuffix;
        $this->xmlPathCreateRedirect = $xmlPathCreateRedirect;
        $this->targetPathBase        = $targetPathBase;
        $this->targetPathIdKey       = $targetPathIdKey;
    }

    /**
     * @param AbstractAttribute|AttributeInterface $attribute
     *
     * @return $this
     */
    public function setAttribute(AbstractAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEntityType()
    {
        return $this->attribute->getEntityType()->getEntityTypeCode();
    }

    /**
     * Returns $storeId => $urlKey pairs
     *
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return array
     */
    private function getFinalUrlKeys(AbstractModel $object)
    {
        $pairs    = $this->getStoreIdValuePairs($object);
        $storeIds = array_keys($this->storeManager->getStores(false));

        $defaultValue = null;
        if (isset($pairs[Store::DEFAULT_STORE_ID])) {
            $defaultValue = $pairs[Store::DEFAULT_STORE_ID];
            unset($pairs[Store::DEFAULT_STORE_ID]);
        }

        $finalValues = [];
        foreach ($storeIds as $storeId) {
            $finalValues[$storeId] = isset($pairs[$storeId]) ?
                $pairs[$storeId] :
                $defaultValue;
        }

        return $finalValues;
    }

    /**
     * Gets $storeId => $existingUrlKeyPairs array
     *
     * @param AbstractModel|AbstractExtensibleModel $object
     *
     * @return array
     */
    private function getStoreIdValuePairs(AbstractModel $object)
    {
        $attribute  = $this->attribute;
        $connection = $this->resource->getConnection();
        $objectId   = $object->getId();

        $entityIdField = 'entity_id';
        if ($attribute->getEntityIdField()) {
            $entityIdField = $attribute->getEntityIdField();
        }

        $select = $connection->select();
        if ($attribute instanceof ScopedAttributeInterface) {
            $select->from(
                $attribute->getBackendTable(),
                [ScopedAttributeInterface::STORE_ID, ScopedAttributeInterface::VALUE]
            )->where(
                $entityIdField . ' =?',
                $objectId
            )->where(
                ScopedAttributeInterface::ATTRIBUTE_ID . ' =?',
                $attribute->getAttributeId()
            );
        } else {
            $select->from(
                $this->getTable(),
                [new \Zend_Db_Expr(Store::DEFAULT_STORE_ID), AttributeInterface::VALUE]
            )->where(
                AttributeInterface::ATTRIBUTE_ID . ' =?',
                $attribute->getAttributeId()
            )->where(
                $entityIdField . ' =?',
                $objectId
            );
        }

        return $connection->fetchPairs($select);
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
     * @param string|null $urlKey
     * @param int|null    $storeId
     *
     * @return null|string
     */
    private function getRequestPath($urlKey, $storeId = null)
    {
        $urlKey = trim($urlKey);
        $suffix = trim($this->getSuffix($storeId));

        return $urlKey ? $urlKey . $suffix : null;
    }

    /**
     * @param string $base
     *
     * @return $this
     */
    public function setTargetPathBase($base)
    {
        $this->targetPathBase = $base;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setTargetPathIdKey($key)
    {
        $this->targetPathIdKey = $key;

        return $this;
    }

    /**
     * @param int   $objectId
     * @param array $additionalParams
     *
     * @return string
     */
    private function getTargetPath($objectId, $additionalParams = [])
    {
        $path = $this->targetPathBase;
        $path .= '/' . $this->targetPathIdKey . '/' . $objectId;

        foreach ($additionalParams as $key => $value) {
            $path .= '/' . $key . '/' . $value;
        }

        return $path;
    }

    /**
     * @param UrlRewrite $rewrite
     *
     * @return string
     */
    private function getArrayKey(UrlRewrite $rewrite)
    {
        return 'store-' . $rewrite->getStoreId() . '-key-' . $rewrite->getRequestPath();
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    protected function createRedirect($storeId)
    {
        if ($this->xmlPathCreateRedirect) {
            if (!isset($this->createRedirectConfig[$storeId])) {
                $this->createRedirectConfig[$storeId] = (bool)$this->scopeConfig->getValue(
                    $this->xmlPathCreateRedirect,
                    ScopeInterface::SCOPE_STORES,
                    $storeId
                );
            }
        } else {
            $this->createRedirectConfig[$storeId] = $this->createRedirectDefaultValue;
        }

        return $this->createRedirectConfig[$storeId];
    }

    /**
     * Generates ALL rewrites for a given object. Also generates 301 redirects
     * for the old url in the case that a url key was changed
     *
     * @param AbstractModel|AbstractExtensibleModel $object
     * @param bool                                  $save
     *
     * @return array
     * @throws UrlAlreadyExistsException
     * @throws LocalizedException
     */
    public function generate(AbstractModel $object, $save = true)
    {
        $urlRewrites = [];

        $urlKeys    = $this->getFinalUrlKeys($object);
        $entityType = $this->getEntityType();
        $objectId   = $object->getId();

        if (!$objectId) {
            throw new LocalizedException(
                __('Object must be saved to the database before generating URL Rewrites.')
            );
        }

        $allRewrites = $this->storage->findAllByData(
            [
                UrlRewrite::ENTITY_ID   => $objectId,
                UrlRewrite::ENTITY_TYPE => $entityType
            ]
        );

        foreach ($allRewrites as $rewrite) {
            $urlRewrites[$this->getArrayKey($rewrite)] = $rewrite;
        }

        foreach ($urlKeys as $storeId => $urlKey) {
            $requestPath = $this->getRequestPath($urlKey, $storeId);
            if (!$requestPath) {
                continue;
            }

            $targetPath = $this->getTargetPath($objectId);
            if (!$targetPath) {
                continue;
            }

            $rewrite = $this->storage->findOneByData(
                [
                    UrlRewrite::STORE_ID      => $storeId,
                    UrlRewrite::ENTITY_ID     => $objectId,
                    UrlRewrite::ENTITY_TYPE   => $entityType,
                    UrlRewrite::REDIRECT_TYPE => 0
                ]
            );

            $redirect = $this->storage->findOneByData(
                [
                    UrlRewrite::STORE_ID      => $storeId,
                    UrlRewrite::ENTITY_ID     => $objectId,
                    UrlRewrite::ENTITY_TYPE   => $entityType,
                    UrlRewrite::REDIRECT_TYPE => 301,
                    UrlRewrite::REQUEST_PATH  => $requestPath
                ]
            );

            /**
             * if there is a 301 for the SAME object with the SAME
             * request path, we just need to flip flop objects. This
             * loop fires when updating to a request path that used to
             * exist for the object and the user wants to restore that
             * request path
             */
            if ($rewrite instanceof UrlRewrite &&
                $redirect instanceof UrlRewrite &&
                $redirect->getRequestPath() === $requestPath
            ) {
                $existingRedirects = $this->storage->findAllByData(
                    [
                        UrlRewrite::STORE_ID      => $storeId,
                        UrlRewrite::ENTITY_ID     => $objectId,
                        UrlRewrite::ENTITY_TYPE   => $entityType,
                        UrlRewrite::REDIRECT_TYPE => 301
                    ]
                );

                foreach ($existingRedirects as $existingRedirect) {
                    if ($existingRedirect->getRequestPath() !== $requestPath) {
                        $existingRedirect->setTargetPath($requestPath);
                        $urlRewrites[$this->getArrayKey($existingRedirect)] = $existingRedirect;
                    }
                }

                $redirect->setRequestPath($rewrite->getRequestPath());
                $redirect->setTargetPath($requestPath);
                $urlRewrites[$this->getArrayKey($redirect)] = $redirect;

                $rewrite->setRequestPath($requestPath);
                $urlRewrites[$this->getArrayKey($rewrite)] = $rewrite;

                continue;
            }

            if (!$rewrite instanceof UrlRewrite) {
                $rewrite = $this->urlRewriteFactory->create();
                $rewrite->setUrlRewriteId(null);
            }

            $oldKey = null;
            if ($rewrite->getUrlRewriteId()) {
                $oldKey = $this->getArrayKey($rewrite);
            }

            $oldPath = $rewrite->getRequestPath();
            $newPath = $requestPath;

            $dataHasChanged = $oldPath !== $newPath && ($rewrite->getRequestPath() !== $requestPath);

            $rewrite->setEntityType($entityType);
            $rewrite->setEntityId($objectId);
            $rewrite->setRequestPath($requestPath);
            $rewrite->setTargetPath($targetPath);
            $rewrite->setRedirectType(0);
            $rewrite->setStoreId($storeId);
            $urlRewrites[$this->getArrayKey($rewrite)] = $rewrite;

            if ($dataHasChanged && $oldPath) {
                $existingRedirects = $this->storage->findAllByData(
                    [
                        UrlRewrite::STORE_ID      => $storeId,
                        UrlRewrite::ENTITY_ID     => $objectId,
                        UrlRewrite::ENTITY_TYPE   => $entityType,
                        UrlRewrite::REDIRECT_TYPE => 301
                    ]
                );

                foreach ($existingRedirects as $existingRedirect) {
                    if ($existingRedirect->getRequestPath() !== $oldPath) {
                        $existingRedirect->setTargetPath($requestPath);
                        $urlRewrites[$this->getArrayKey($existingRedirect)] = $existingRedirect;
                    }
                }

                if ($this->createRedirect($storeId)) {
                    $redirect = $this->urlRewriteFactory->create();
                    $redirect->setUrlRewriteId(null);
                    $redirect->setEntityType($entityType);
                    $redirect->setEntityId($objectId);
                    $redirect->setRequestPath($oldPath);
                    $redirect->setTargetPath($newPath);
                    $redirect->setRedirectType(301);
                    $redirect->setStoreId($storeId);
                    $urlRewrites[$this->getArrayKey($redirect)] = $redirect;
                } elseif ($oldKey) {
                    if (isset($urlRewrites[$oldKey])) {
                        unset($urlRewrites[$oldKey]);
                    }
                }
            }
        }

        if (!empty($urlRewrites) && $save) {
            $this->storage->replace($urlRewrites);
        }

        return $urlRewrites;
    }
}
