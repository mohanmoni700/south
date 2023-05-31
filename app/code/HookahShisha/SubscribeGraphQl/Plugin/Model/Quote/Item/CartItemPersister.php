<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Quote\Item;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemPersister as Subject;

class CartItemPersister
{
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
    }

    public function aroundSave(Subject $subject, callable $proceed, CartInterface $quote, CartItemInterface $item)
    {
        if (!$this->cartItemSubscribeDataRegistry->isDataSet()) {
            return $proceed($quote, $item);
        } else {
            return $item;
        }
    }
}
