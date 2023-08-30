<?php

namespace Corra\AmastyPromoGraphQl\Model\Resolver;

use Amasty\Promo\Helper\Item;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get free gift SKU's
 */
class GetFreeGiftSkus implements ResolverInterface
{

    /**
     * @var Item
     */
    private $promoItemHelper;

    /**
     * GetFreeGiftSkus constructor.
     * @param Item $promoHelper
     */
    public function __construct(
        Item $promoItemHelper
    )
    {
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cart = $value['model'];
        $cartAppliedRules = $cart->getAppliedRuleIds();
        $freeGiftSkus = [];
        if ($cartAppliedRules) {
            $cartItems = $cart->getAllItems();
            foreach ($cartItems as $item) {
                if ($this->promoItemHelper->isPromoItem($item)) {
                        $sku = $item->getProduct()->getData('sku');
                        $freeGiftSkus[] = $sku;
                }
            }
        }
        return $freeGiftSkus;
    }
}
