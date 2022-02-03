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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class IntegerList
 *
 * @package MageModule\Core\Model\Entity\Attribute\Backend
 */
class IntegerList extends \MageModule\Core\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value         = $object->getData($attributeCode);

        if (is_array($value)) {
            asort($value);
            $value = str_replace(' ', '', implode(',', array_unique($value)));
            $object->setData($attributeCode, $value);
        } elseif (is_string($value)) {
            $value = explode(',', $value);
            $object->setData($attributeCode, $value);

            return $this->beforeSave($object);
        }

        $this->validate($object);

        return parent::beforeSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        parent::validate($object);

        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getName();
        $value     = $object->getData($attrCode);

        if (!$attribute->isValueEmpty($value)) {
            $values        = explode(',', $value);
            $invalidValues = [];
            foreach ($values as $value) {
                if (!ctype_digit($value)) {
                    $invalidValues[] = $value;
                }
            }

            $label = $attribute->getDefaultFrontendLabel();
            if (!empty($invalidValues)) {
                if (count($invalidValues) === 1) {
                    throw new LocalizedException(
                        __(
                            '%1 is an invalid value for %2. Only integers are allowed.',
                            implode(', ', $invalidValues),
                            $label
                        )
                    );
                } else {
                    throw new LocalizedException(
                        __(
                            '%1 are invalid values for %2. Only integers are allowed.',
                            implode(', ', $invalidValues),
                            $label
                        )
                    );
                }
            }
        }

        return true;
    }
}
