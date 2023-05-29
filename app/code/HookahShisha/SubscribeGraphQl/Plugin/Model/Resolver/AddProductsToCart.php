<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart as Subject;

class AddProductsToCart
{
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
    ) {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
    }

    /**
     * @param Subject $subject
     * @param ...$functionArgs
     * @return array
     */
    public function beforeResolve(Subject $subject, ...$functionArgs)
    {
        $args = $functionArgs[4] ?? [];
        $this->cartItemSubscribeDataRegistry->setData($args['cartItems']);
        return $functionArgs;
    }
}
