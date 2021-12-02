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
 * Data provider for alfa bundle product buy requests
 */
class AlfaBundleDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @inheritdoc
     */
    public function execute(CartItem $cartItem): array
    {
        return ['alfa_bundle' => $cartItem->getAlfaBundle() ?? null];
    }
}
