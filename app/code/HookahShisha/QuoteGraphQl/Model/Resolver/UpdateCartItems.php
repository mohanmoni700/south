<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\UpdateCartItems as UpdateCartItemsProvider;
use Magento\QuoteGraphQl\Model\Resolver\UpdateCartItems as SourceUpdateCartItems;

/**
 * Resolver for updateCartItems mutation
 */
class UpdateCartItems extends SourceUpdateCartItems
{
    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var UpdateCartItemsProvider
     */
    private UpdateCartItemsProvider $updateCartItems;

    /**
     * @var ArgumentsProcessorInterface
     */
    private ArgumentsProcessorInterface $argsSelection;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param UpdateCartItemsProvider $updateCartItems
     * @param ArgumentsProcessorInterface $argsSelection
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        UpdateCartItemsProvider $updateCartItems,
        ArgumentsProcessorInterface $argsSelection
    ) {
        parent::__construct($getCartForUser, $cartRepository, $updateCartItems, $argsSelection);

        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->updateCartItems = $updateCartItems;
        $this->argsSelection = $argsSelection;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $processedArgs = $this->argsSelection->process($info->fieldName, $args);

        if (empty($processedArgs['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        $maskedCartId = $processedArgs['input']['cart_id'];

        if (empty($processedArgs['input']['cart_items'])
            || !is_array($processedArgs['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing.'));
        }

        $cartItems = $processedArgs['input']['cart_items'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $cartItems = $this->addAlfaBundleProductsToCartItems($cart, $cartItems);

        try {
            $this->updateCartItems->processCartItems($cart, $cartItems);
            $this->cartRepository->save($cart);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Adds alfa bundle products to cartItems so that they are updated when main product is updated
     *
     * @param Quote $cart
     * @param array $cartItems
     * @return array
     */
    private function addAlfaBundleProductsToCartItems(Quote $cart, array $cartItems): array
    {
        foreach ($cartItems as $cartItem) {
            $itemInCart = $cart->getItemById($cartItem['cart_item_id']);
            $qtyToAdd = $cartItem['quantity'] - $itemInCart->getQty();
            $bundleProducts = $itemInCart->getAlfaBundle();

            if ($bundleProducts) {
                $bundleProductsDecoded = json_decode($itemInCart->getAlfaBundle(), true);

                $bundleItemsToUpdate = array_filter($cart->getItems(), function ($item) use ($bundleProductsDecoded) {
                    $itemSku = $item->getSku();

                    if ($itemSku == $bundleProductsDecoded['shisha_sku']
                        || $itemSku == $bundleProductsDecoded['charcoal_sku']
                    ) {
                        return $item;
                    }

                    return false;
                });

                foreach ($bundleItemsToUpdate as $bundleItemToUpdate) {
                    $itemToPush = [
                        'quantity' => $bundleItemToUpdate->getQty() + $qtyToAdd,
                        'cart_item_id' => $bundleItemToUpdate->getId()
                    ];

                    array_push($cartItems, $itemToPush);
                }
            }
        }

        return $cartItems;
    }
}
