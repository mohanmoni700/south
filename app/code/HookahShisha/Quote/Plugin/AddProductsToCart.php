<?php

namespace HookahShisha\Quote\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as SourceAddProductsToCart;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class AddProductsToCart
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * AAAAAAAAAAAAAAAAAAAAAAAAA
     *
     * @param SourceAddProductsToCart $subject
     * @param AddProductsToCartOutput $result
     * @param string $maskedCartId
     * @param array $cartItems
     * @return AddProductsToCartOutput
     * @throws NoSuchEntityException
     */
    public function afterExecute(
        SourceAddProductsToCart $subject,
        AddProductsToCartOutput $result,
        string $maskedCartId,
        array $cartItems
    ): AddProductsToCartOutput {
//        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
//        $cart = $this->cartRepository->get($cartId);
//
//        $addedCartItems = $cart->getItems();
//
//        foreach ($addedCartItems as $addedCartItem) {
//            if ($addedCartItem->getProductType() == 'configurable') {
//                $addedCartItem->setAlfaBundle($cartItems[0]->getAlfaBundle());
//            }
//        }
//
//        $this->cartRepository->save($cart);
//        $cart->collectTotals();

        return $result;
    }
}
