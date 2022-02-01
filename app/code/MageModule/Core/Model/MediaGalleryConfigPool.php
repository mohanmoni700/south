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

class MediaGalleryConfigPool implements MediaGalleryConfigPoolInterface
{
    /**
     * @var MediaGalleryConfigInterface[]
     */
    private $pool;

    /**
     * MediaGalleryConfigPool constructor.
     *
     * @param MediaGalleryConfigInterface[] $configs
     */
    public function __construct(
        array $configs = []
    ) {
        $this->pool = $configs;
    }

    /**
     * @param string                      $attributeCode
     * @param MediaGalleryConfigInterface $config
     *
     * @return $this
     */
    public function addConfig($attributeCode, MediaGalleryConfigInterface $config)
    {
        $this->pool[$attributeCode] = $config;

        return $this;
    }

    /**
     * @param $attributeCode
     *
     * @return bool|MediaGalleryConfigInterface
     */
    public function getConfig($attributeCode)
    {
        if (isset($this->pool[$attributeCode])) {
            return $this->pool[$attributeCode];
        }

        return false;
    }
}
