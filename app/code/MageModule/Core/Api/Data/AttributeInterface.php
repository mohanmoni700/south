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
 * Interface AttributeInterface
 *
 * @package MageModule\Core\Api\Data
 */
interface AttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    const VALUE                 = 'value';
    const VALUE_ID              = 'value_id';
    const IS_VISIBLE            = 'is_visible';
    const IS_WYSIWYG_ENABLED    = 'is_wysiwyg_enabled';
    const IS_USED_IN_GRID       = 'is_used_in_grid';
    const IS_VISIBLE_IN_GRID    = 'is_visible_in_grid';
    const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';

    /**
     * @param int|bool $isVisible
     *
     * @return $this
     */
    public function setIsVisible($isVisible);

    /**
     * @return bool
     */
    public function getIsVisible();

    /**
     * @param int|bool $isWysiwygEnabled
     *
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled);

    /**
     * @return bool
     */
    public function getIsWysiwygEnabled();

    /**
     * @param int|bool $isUsedInGrid
     *
     * @return $this
     */
    public function setIsUsedInGrid($isUsedInGrid);

    /**
     * @return bool
     */
    public function getIsUsedInGrid();

    /**
     * @param int|bool $isVisibleInGrid
     *
     * @return $this
     */
    public function setIsVisibleInGrid($isVisibleInGrid);

    /**
     * @return bool
     */
    public function getIsVisibleInGrid();

    /**
     * @param int|bool $isFilterableInGrid
     *
     * @return $this
     */
    public function setIsFilterableInGrid($isFilterableInGrid);

    /**
     * @return bool
     */
    public function getIsFilterableInGrid();
}
