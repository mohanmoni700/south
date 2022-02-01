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

use MageModule\Core\Api\Data\AttributeInterface;
use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

abstract class AbstractBackend extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * AbstractBackend constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param DataObject $object
     *
     * @return int|null
     */
    public function getObjectId($object)
    {
        $objectId = $object->getData('id');
        if ($object instanceof AbstractExtensibleModel) {
            $objectId = $object->getEntityId();
        } elseif ($object instanceof AbstractModel) {
            $objectId = $object->getId();
        }

        return $objectId;
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return array
     */
    protected function getStoreIdValuePairs($object)
    {
        $attribute  = $this->getAttribute();
        $connection = $this->resource->getConnection();
        $objectId   = $this->getObjectId($object);

        $select = $connection->select();
        if ($attribute instanceof ScopedAttributeInterface) {
            $select->from(
                $this->getTable(),
                [ScopedAttributeInterface::STORE_ID, ScopedAttributeInterface::VALUE]
            )->where(
                $this->getEntityIdField() . ' =?',
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
                $this->getEntityIdField() . ' =?',
                $objectId
            );
        }

        return $connection->fetchPairs($select);
    }

    /**
     * @param DataObject $object
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        parent::validate($object);

        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if (!$object->getData(AbstractExtensibleModel::STORE_ID) &&
            $attribute->getIsVisible() &&
            $attribute->getIsRequired() &&
            $attribute->isValueEmpty($value)
        ) {
            $label = $attribute->getFrontend()->getLabel();
            throw new LocalizedException(__('The value of attribute "%1" must be set', $label));
        }

        return true;
    }
}
