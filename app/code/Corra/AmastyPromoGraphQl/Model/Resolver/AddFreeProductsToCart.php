<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Corra\AmastyPromoGraphQl\Model\Cart\AddFreeProductToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Add free products to cart GraphQl resolver
 */
class AddFreeProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddFreeProductToCart
     */
    private $addFreeProductToCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddFreeProductToCart $addFreeProductToCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddFreeProductToCart $addFreeProductToCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addFreeProductToCart = $addFreeProductToCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing'));
        }
        $cartItems = $args['input']['cart_items'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->addFreeProductToCart->execute($cart, $cartItems);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
