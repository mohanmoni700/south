<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart as Subject;

class AddProductsToCart
{
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;

    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
    }
    public function beforeResolve(Subject $subject, ...$functionArgs)
    {
        $args = $functionArgs[4];
        $this->cartItemSubscribeDataRegistry->setData($args['cartItems']);
        return $functionArgs;
    }
}
