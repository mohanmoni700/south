<?php

namespace MageModule\Core\Model\Entity\Attribute\Backend;

use Magento\Framework\DataObject;

/**
 * Class JsonEncoded
 *
 * @package MageModule\Core\Model\Entity\Attribute\Backend
 */
class JsonEncoded extends \Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded
{
    /**
     * Fixes issue in which a value of false does not trigger value deletion
     *
     * @param DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode) && $object->getData($attrCode) === false) {
            return $this;
        }

        return parent::beforeSave($object);
    }

    /**
     * @param DataObject $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if (!$object->hasData($attrCode)) {
            $object->setData($attrCode, []);

            return $this;
        }

        return parent::afterLoad($object);
    }
}
