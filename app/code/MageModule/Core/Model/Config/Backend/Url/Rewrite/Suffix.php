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

namespace MageModule\Core\Model\Config\Backend\Url\Rewrite;

use MageModule\Core\Api\AttributeRepositoryInterface;
use MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\UrlRewrite\Helper\UrlRewrite as UrlRewriteHelper;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Suffix extends \Magento\Framework\App\Config\Value
{
    /**
     * @var string
     */
    private $urlKeyAttributeCode;

    /**
     * @var UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var AbstractCollection
     */
    private $collection;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var UrlRewriteHelper
     */
    private $urlRewriteHelper;

    /**
     * @var \Magento\Framework\App\Config
     */
    private $appConfig;

    /**
     * Suffix constructor.
     *
     * @param AbstractCollection           $collection
     * @param AttributeRepositoryInterface $attributeRepository
     * @param UrlRewriteGenerator          $urlRewriteGenerator
     * @param string                       $urlKeyAttributeCode
     * @param UrlRewriteHelper             $urlRewriteHelper
     * @param Context                      $context
     * @param Registry                     $registry
     * @param AppConfig                    $appConfig
     * @param ScopeConfigInterface         $config
     * @param TypeListInterface            $cacheTypeList
     * @param AbstractResource|null        $resource
     * @param AbstractDb|null              $resourceCollection
     * @param array                        $data
     */
    public function __construct(
        AbstractCollection $collection,
        AttributeRepositoryInterface $attributeRepository,
        UrlRewriteGenerator $urlRewriteGenerator,
        $urlKeyAttributeCode,
        UrlRewriteHelper $urlRewriteHelper,
        Context $context,
        Registry $registry,
        AppConfig $appConfig,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlKeyAttributeCode = $urlKeyAttributeCode;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->urlRewriteHelper    = $urlRewriteHelper;
        $this->attributeRepository = $attributeRepository;
        $this->collection          = $collection;
        $this->appConfig           = $appConfig;
    }

    /**
     * @return $this|AppConfig\Value
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $this->urlRewriteHelper->validateSuffix($this->getValue());

        return $this;
    }

    /**
     * @return AppConfig\Value
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->updateUrlRewrites();
        }

        return parent::afterSave();
    }

    /**
     * @return AppConfig\Value
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function afterDeleteCommit()
    {
        $this->updateUrlRewrites();

        return parent::afterDeleteCommit();
    }

    /**
     * Regenerates url_rewrite table entries for the collection
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    private function updateUrlRewrites()
    {
        $this->appConfig->clean();

        $attribute = $this->attributeRepository->get($this->urlKeyAttributeCode);

        $attributeEntityTypeId  = (int)$attribute->getEntityTypeId();
        $collectionEntityTypeId = (int)$this->collection->getEntity()
            ->getEntityType()
            ->getEntityTypeId();

        if ($attributeEntityTypeId !== $collectionEntityTypeId) {
            throw new LocalizedException(
                __(
                    'Cannot generate URL rewrites for collection. The attribute\'s 
                entity type ID does not match the collection\'s entity type ID.'
                )
            );
        }

        $this->urlRewriteGenerator->setAttribute($attribute);

        foreach ($this->collection as $object) {
            $this->urlRewriteGenerator->generate($object, true);
        }

        return $this;
    }
}
