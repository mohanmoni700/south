<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Add products to cart
 */
class AddFreeProductToCart
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AddFreeProduct
     */
    private $addProductToCart;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param AddFreeProduct $addProductToCart
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddFreeProduct $addProductToCart
    ) {
        $this->cartRepository = $cartRepository;
        $this->addProductToCart = $addProductToCart;
    }

    /**
     * Add products to cart
     *
     * @param Quote $cart
     * @param array $cartItems
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItems): void
    {
        foreach ($cartItems as $cartItemData) {
            if ($cartItemData['data']['max_qty'] > 0) {
                $this->addProductToCart->execute($cart, $cartItemData);
            } else {
                throw new GraphQlInputException(__('Allowed maximum quantity reached.'));
            }
        }

        if ($cart->getData('has_error')) {
            $e = new GraphQlInputException(__('Shopping cart errors'));
            $errors = $cart->getErrors();
            foreach ($errors as $error) {
                /** @var MessageInterface $error */
                $e->addError(new GraphQlInputException(__($error->getText())));
            }
            throw $e;
        }

        $this->cartRepository->save($cart);
    }
}
