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

namespace MageModule\Core\Model\Entity\Attribute\Backend;

use MageModule\Core\Model\ResourceModel\Entity\UrlKeyGenerator;
use MageModule\Core\Model\ResourceModel\Entity\UrlRewriteGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

class UrlKey extends \MageModule\Core\Model\Entity\Attribute\Backend\UrlKeyFormat
{
    /**
     * @var UrlKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @var UrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * UrlKey constructor.
     *
     * @param UrlKeyGenerator     $urlKeyGenerator
     * @param UrlRewriteGenerator $urlRewriteGenerator
     * @param StorageInterface    $storage
     * @param ResourceConnection  $resource
     * @param FilterManager       $filterManager
     */
    public function __construct(
        UrlKeyGenerator $urlKeyGenerator,
        UrlRewriteGenerator $urlRewriteGenerator,
        StorageInterface $storage,
        ResourceConnection $resource,
        FilterManager $filterManager
    ) {
        parent::__construct($resource, $filterManager);

        $this->urlKeyGenerator     = $urlKeyGenerator;
        $this->urlRewriteGenerator = $urlRewriteGenerator;
        $this->storage             = $storage;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if ($value && $object->dataHasChangedFor($attrCode)) {
            $this->urlKeyGenerator->setAttribute($this->getAttribute());
            $value = $this->urlKeyGenerator->generate($object);
            if ($value) {
                $object->setData($attrCode, $value);
            }
        }

        $this->validate($object);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     * @throws UrlAlreadyExistsException
     */
    public function afterSave($object)
    {
        parent::afterSave($object);

        $this->urlRewriteGenerator->setAttribute($this->getAttribute());
        $this->urlRewriteGenerator->generate($object, true);

        return $this;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     */
    public function afterDelete($object)
    {
        $this->storage->deleteByData(
            [
                UrlRewrite::ENTITY_TYPE => $this->getAttribute()->getEntityType()->getEntityTypeCode(),
                UrlRewrite::ENTITY_ID   => $this->getObjectId($object)
            ]
        );

        return parent::afterDelete($object);
    }
}
