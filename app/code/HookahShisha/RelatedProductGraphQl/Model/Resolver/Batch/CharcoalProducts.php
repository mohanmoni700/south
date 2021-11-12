<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_RelatedProductGraphQl
 * @author    Janis Verins <info@corra.com
 */
declare(strict_types=1);

namespace HookahShisha\RelatedProductGraphQl\Model\Resolver\Batch;

use HookahShisha\Catalog\Model\Product\Link;
use Magento\RelatedProductGraphQl\Model\Resolver\Batch\AbstractLikedProducts;

/**
 * Charcoal Products Resolver
 */
class CharcoalProducts extends AbstractLikedProducts
{
    /**
     * @inheritDoc
     */
    protected function getNode(): string
    {
        return 'charcoal_products';
    }

    /**
     * @inheritDoc
     */
    protected function getLinkType(): int
    {
        return Link::LINK_TYPE_CHARCOAL;
    }
}
