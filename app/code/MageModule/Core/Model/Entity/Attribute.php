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

class Attribute extends \Magento\Eav\Model\Entity\Attribute implements
    \MageModule\Core\Api\Data\AttributeInterface
{
    /**
     * @param int|bool $isVisible
     *
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        $this->setData(self::IS_VISIBLE, (int)$isVisible);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVisible()
    {
        return (bool)$this->getData(self::IS_VISIBLE);
    }

    /**
     * @param int|bool $isWysiwygEnabled
     *
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        $this->setData(self::IS_WYSIWYG_ENABLED, (int)$isWysiwygEnabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsWysiwygEnabled()
    {
        return (bool)$this->getData(self::IS_WYSIWYG_ENABLED);
    }

    /**
     * @param int|bool $isUsedInGrid
     *
     * @return $this
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        $this->setData(self::IS_USED_IN_GRID, (int)$isUsedInGrid);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsUsedInGrid()
    {
        return (bool)$this->getData(self::IS_USED_IN_GRID);
    }

    /**
     * @param int|bool $isVisibleInGrid
     *
     * @return $this
     */
    public function setIsVisibleInGrid($isVisibleInGrid)
    {
        $this->setData(self::IS_VISIBLE_IN_GRID, (int)$isVisibleInGrid);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVisibleInGrid()
    {
        return (bool)$this->getData(self::IS_VISIBLE_IN_GRID);
    }

    /**
     * @param int|bool $isFilterableInGrid
     *
     * @return $this
     */
    public function setIsFilterableInGrid($isFilterableInGrid)
    {
        $this->setData(self::IS_FILTERABLE_IN_GRID, (int)$isFilterableInGrid);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFilterableInGrid()
    {
        return (bool)$this->getData(self::IS_FILTERABLE_IN_GRID);
    }
}
