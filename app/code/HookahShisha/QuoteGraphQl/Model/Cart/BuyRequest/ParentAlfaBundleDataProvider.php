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
 * Data provider for parent_alfa_bundle buy requests
 */
class ParentAlfaBundleDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @inheritdoc
     */
    public function execute(CartItem $cartItem): array
    {
        return ['parent_alfa_bundle' => $cartItem->getParentAlfaBundle() ?? null];
    }
}
