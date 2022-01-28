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

namespace MageModule\Core\Api\Data;

/**
 * Interface ScopedAttributeInterface
 *
 * @package MageModule\Core\Api\Data
 */
interface ScopedAttributeInterface extends \MageModule\Core\Api\Data\AttributeInterface
{
    const STORE_ID           = 'store_id';
    const IS_GLOBAL          = 'is_global';
    const SCOPE_STORE_TEXT   = 'store';
    const SCOPE_GLOBAL_TEXT  = 'global';
    const SCOPE_WEBSITE_TEXT = 'website';

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope);

    /**
     * @return string|null
     */
    public function getScope();

    /**
     * @return bool
     */
    public function isScopeGlobal();

    /**
     * @return bool
     */
    public function isScopeWebsite();

    /**
     * @return bool
     */
    public function isScopeStore();
}
