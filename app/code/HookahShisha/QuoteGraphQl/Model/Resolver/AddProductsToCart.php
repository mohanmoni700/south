<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use HookahShisha\Quote\Model\Cart\Data\CartItemFactory;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor\ItemDataProcessorInterface;
use Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart as SourceAddProductsToCart;

/**
 * Resolver for addProductsToCart mutation
 */
class AddProductsToCart extends SourceAddProductsToCart
{
    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var AddProductsToCartService
     */
    private AddProductsToCartService $addProductsToCartService;

    /**
     * @var ItemDataProcessorInterface
     */
    private ItemDataProcessorInterface $itemDataProcessor;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCartService $addProductsToCart
     * @param ItemDataProcessorInterface $itemDataProcessor
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCartService $addProductsToCart,
        ItemDataProcessorInterface $itemDataProcessor
    ) {
        parent::__construct($getCartForUser, $addProductsToCart, $itemDataProcessor);

        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCartService = $addProductsToCart;
        $this->itemDataProcessor = $itemDataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['cartId'])) {
            throw new GraphQlInputException(__('Required parameter "cartId" is missing'));
        }
        if (empty($args['cartItems']) || !is_array($args['cartItems'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cartItems" is missing'));
        }

        $maskedCartId = $args['cartId'];
        $cartItemsData = $args['cartItems'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        // Shopping Cart validation
        $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $cartItems = [];
        foreach ($cartItemsData as $cartItemData) {
            if (!$this->itemIsAllowedToCart($cartItemData, $context)) {
                continue;
            }
            $cartItems[] = (new CartItemFactory())->create($cartItemData);
        }

        $addProductsToCartOutput = $this->addProductsToCartService->execute($maskedCartId, $cartItems);

        return [
            'cart' => [
                'model' => $addProductsToCartOutput->getCart(),
            ],
            'user_errors' => array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                        'path' => [$error->getCartItemPosition()]
                    ];
                },
                $addProductsToCartOutput->getErrors()
            )
        ];
    }

    /**
     * Check if the item can be added to cart
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     * @return bool
     */
    private function itemIsAllowedToCart(array $cartItemData, ContextInterface $context): bool
    {
        $cartItemData = $this->itemDataProcessor->process($cartItemData, $context);
        if (isset($cartItemData['grant_checkout']) && $cartItemData['grant_checkout'] === false) {
            return false;
        }

        return true;
    }
}
