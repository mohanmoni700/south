<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Model\Product;

use Magento\Catalog\Model\Product\Link as SourceLink;

/**
 * Catalog product link model for Shisha and Charcoal products
 */
class Link extends SourceLink
{
    const LINK_TYPE_SHISHA = 7;

    const LINK_TYPE_CHARCOAL = 8;

    /**
     * Sets Shisha link type id
     *
     * @return $this
     */
    public function useShishaLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_SHISHA);
        return $this;
    }

    /**
     * Sets Charcoal link type id
     *
     * @return $this
     */
    public function useCharCoalLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_CHARCOAL);
        return $this;
    }
}
