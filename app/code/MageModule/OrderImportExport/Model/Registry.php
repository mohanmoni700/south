<?php
/**
 * Copyright (c) 2019 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Model;

/**
 * Class Registry
 *
 * @package MageModule\OrderImportExport\Model
 */
class Registry
{
    /**
     * @var bool
     */
    private $isOrderImportExport = false;

    /**
     * @param bool $bool
     *
     * @return $this
     */
    public function setIsOrderImportExport($bool)
    {
        $this->isOrderImportExport = (bool)$bool;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOrderImportExport()
    {
        return $this->isOrderImportExport;
    }
}
