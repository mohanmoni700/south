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
use MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

/**
 * Class InsertUrlRewriteValues
 *
 * @package MageModule\Core\Observer\Store\Add
 */
class InsertUrlRewriteValues implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var AbstractCollection
     */
    private $collection;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var string
     */
    private $urlKeyAttributeCode;

    /**
     * InsertUrlRewriteValues constructor.
     *
     * @param AbstractCollection           $collection
     * @param AttributeRepositoryInterface $attributeRepository
     * @param UrlRewriteGenerator          $urlRewriteGenerator
     * @param string                       $urlKeyAttributeCode
     */
    public function __construct(
        AbstractCollection $collection,
        AttributeRepositoryInterface $attributeRepository,
        UrlRewriteGenerator $urlRewriteGenerator,
        $urlKeyAttributeCode
    ) {
        $this->collection          = $collection;
        $this->attributeRepository = $attributeRepository;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->urlKeyAttributeCode = $urlKeyAttributeCode;
    }

    /**
     * When a new store view is added, regenerates url_rewrite table entries for the collection
     *
     * @param Observer $observer
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
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
    }
}
