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

namespace MageModule\Core\Model;

/**
 * Interface MediaGalleryConfigPoolInterface
 *
 * @package MageModule\Core\Model
 */
interface MediaGalleryConfigPoolInterface
{
    /**
     * @param string                      $attributeCode
     * @param MediaGalleryConfigInterface $config
     *
     * @return $this
     */
    public function addConfig($attributeCode, MediaGalleryConfigInterface $config);

    /**
     * @param $attributeCode
     *
     * @return bool|MediaGalleryConfigInterface
     */
    public function getConfig($attributeCode);
}
