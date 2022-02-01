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

namespace MageModule\Core\Model\Entity;

/**
 * Class ScopedAttribute
 *
 * @package MageModule\Core\Model\Entity
 */
class ScopedAttribute extends \MageModule\Core\Model\Entity\Attribute implements
    \MageModule\Core\Api\Data\ScopedAttributeInterface,
    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface
{
    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        switch ($scope) {
            case self::SCOPE_GLOBAL_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_GLOBAL);
                break;
            case self::SCOPE_WEBSITE_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_WEBSITE);
                break;
            case self::SCOPE_STORE_TEXT:
                $this->setData(self::IS_GLOBAL, self::SCOPE_STORE);
                break;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope()
    {
        $scope = $this->getData(self::IS_GLOBAL);
        switch ($scope) {
            case self::SCOPE_GLOBAL:
                $result = self::SCOPE_GLOBAL_TEXT;
                break;
            case self::SCOPE_WEBSITE:
                $result = self::SCOPE_WEBSITE_TEXT;
                break;
            case self::SCOPE_STORE:
                $result = self::SCOPE_STORE_TEXT;
                break;
            default:
                $result = null;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getScope() === self::SCOPE_GLOBAL_TEXT;
    }

    /**
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getScope() === self::SCOPE_WEBSITE_TEXT;
    }

    /**
     * @return bool
     */
    public function isScopeStore()
    {
        return $this->getScope() === self::SCOPE_STORE_TEXT;
    }
}
