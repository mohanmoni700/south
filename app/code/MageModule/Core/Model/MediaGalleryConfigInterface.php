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

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface MediaGalleryConfigInterface
 *
 * @package MageModule\Core\Model
 */
interface MediaGalleryConfigInterface
{
    /**
     * @return array|string[]
     */
    public function getAllowedExtensions();

    /**
     * @return string
     */
    public function getUploadControllerRoute();

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseTmpMediaUrl();

    /**
     * @param string $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTmpMediaUrl($file);

    /**
     * @return string
     */
    public function getBaseTmpMediaPath();

    /**
     * @param string $file
     *
     * @return string
     */
    public function getTmpMediaPath($file);

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl();

    /**
     * @param string $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl($file);

    /**
     * @return string
     */
    public function getBaseMediaPath();

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaPath($file);

    /**
     * @param string $file
     *
     * @return string
     */
    public function getMediaDir($file);
}
