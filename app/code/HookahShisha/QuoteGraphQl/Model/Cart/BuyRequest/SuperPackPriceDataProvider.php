<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Quote\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;
use Magento\Quote\Model\Cart\Data\CartItem;

/**
 * Data provider for super_pack_price buy requests
 */
class SuperPackPriceDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @inheritdoc
     */
    public function execute(CartItem $cartItem): array
    {
        return ['super_pack_price' => $cartItem->getSuperPackPrice() ?? null];
    }
}
