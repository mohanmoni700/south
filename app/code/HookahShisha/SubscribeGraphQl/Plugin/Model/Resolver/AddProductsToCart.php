<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart as Subject;

/**
 * AddProductsToCart
 */
class AddProductsToCart
{
    /**
     * @var CartItemSubscribeDataRegistry
     */
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
        $cartItems = $args['cartItems'];
        $data = [];
        foreach ($cartItems as $cartItem) {
            $sku = $cartItem['sku'] ?? null;
            if ($sku) {
                $data[$sku] = $cartItem;
            }
        }
        $this->cartItemSubscribeDataRegistry->setData($data);
        return $functionArgs;
    }
}
