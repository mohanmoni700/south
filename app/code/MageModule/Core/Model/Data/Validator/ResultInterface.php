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

namespace MageModule\Core\Model\Data\Validator;

/**
 * Interface ResultInterface
 *
 * @package MageModule\Core\Model\Data\Validator
 */
interface ResultInterface
{
    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return null|string
     */
    public function getMessage();

    /**
     * Data about what specifically is invalid. For example, the names of missing columns
     *
     * @return array
     */
    public function getInvalidData();
}
