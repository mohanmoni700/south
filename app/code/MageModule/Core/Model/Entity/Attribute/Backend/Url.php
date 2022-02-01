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

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;

class Url extends \MageModule\Core\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @param DataObject|AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave($object)
    {
        $this->validate($object);

        return parent::beforeSave($object);
    }

    /**
     * @param DataObject|AbstractModel $object
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value    = $object->getData($attrCode);

        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new LocalizedException(
                __('%1 is not a valid URL.', $value)
            );
        }

        return parent::validate($object);
    }
}
