<?php

namespace Magedelight\Subscribenow\Model\Attribute\Backend;

/**
 * Class Trialcycle
 */
class Dayofmonth extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var int $minimumValueLength
     */
    protected $minimumValueLength = 0;
    protected $maximumValueLength = 31;

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        // your after load logic

        return parent::afterLoad($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->validateLength($object);

        return parent::beforeSave($object);
    }

    /**
     * Validate length
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateLength($object)
    {
        /** @var string $attributeCode */
        $attributeCode = $this->getAttribute()->getAttributeCode();
        /** @var int $value */
        $value = (int) $object->getData($attributeCode);
        /** @var int $minimumValueLength */
        $minimumValueLength = $this->getMinimumValueLength();
        /** @var int $maximumValueLength */
        $maximumValueLength = $this->getMaximumValueLength();

        if ($value) {
            if ($value <= $minimumValueLength || $value > $maximumValueLength) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The value of attribute "Day Of Month" must be between 0 to 32')
                );
            }
        }

        return true;
    }

    /**
     * Get minimum attribute value length
     *
     * @return int
     */
    public function getMinimumValueLength()
    {
        return $this->minimumValueLength;
    }

    public function getMaximumValueLength()
    {
        return $this->maximumValueLength;
    }
}
