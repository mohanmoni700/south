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
use Magento\Framework\Serialize\Serializer\Json;
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
     * @var Json
     */
    private Json $serializer;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param UpdateCartItemsProvider $updateCartItems
     * @param Json $serializer
     * @param ArgumentsProcessorInterface $argsSelection
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        UpdateCartItemsProvider $updateCartItems,
        Json $serializer,
        ArgumentsProcessorInterface $argsSelection
    ) {
        parent::__construct($getCartForUser, $cartRepository, $updateCartItems, $argsSelection);

        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->updateCartItems = $updateCartItems;
        $this->argsSelection = $argsSelection;
        $this->serializer = $serializer;
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
            $alfaBundle = $itemInCart->getAlfaBundle();

            if ($alfaBundle) {
                $bundleItemsToUpdate = array_filter($cart->getItems(), function ($item) use ($alfaBundle) {
                    if ($item->getParentAlfaBundle() && $item->getParentAlfaBundle() == $alfaBundle) {
                        return $item;
                    }
                    return false;
                });

                $alfaBundleItem = $this->serializer->unserialize($alfaBundle);
                $cartItems = $this->getBundledCartItems($alfaBundleItem, $bundleItemsToUpdate, $qtyToAdd, $cartItems);
            }
        }

        return $cartItems;
    }

    /**
     * Get bundled or super pack product with updated qty
     *
     * @param array $alfaBundleItem
     * @param array $bundleItemsToUpdate
     * @param float $qtyToAdd
     * @param array $cartItems
     * @return array
     */
    private function getBundledCartItems(
        $alfaBundleItem,
        array $bundleItemsToUpdate,
        float $qtyToAdd,
        array $cartItems
    ): array {
        // Check in super_pack array to get the count of same item
        // qty to increase * count of same item in array
        if (isset($alfaBundleItem['super_pack']) && $alfaBundleItem['super_pack']) {
            // get all variant sku
            $allVariantSku = array_map(function ($item) {
                return $item['variant_sku'];
            }, $alfaBundleItem['super_pack']);

            // get count of same variant sku.
            $superPackVariantCount = array_count_values($allVariantSku);
            foreach ($bundleItemsToUpdate as $bundleItemToUpdate) {
                $itemToPush = [
                    'quantity' =>
                        $bundleItemToUpdate->getQty() +
                        ($qtyToAdd * ($superPackVariantCount[$bundleItemToUpdate->getSku()] ?? 1))
                    ,
                    'cart_item_id' => $bundleItemToUpdate->getId()
                ];

                $cartItems[] = $itemToPush;
            }
        } else {
            foreach ($bundleItemsToUpdate as $bundleItemToUpdate) {
                $itemToPush = [
                    'quantity' => $bundleItemToUpdate->getQty() + $qtyToAdd,
                    'cart_item_id' => $bundleItemToUpdate->getId()
                ];

                $cartItems[] = $itemToPush;
            }
        }
        return $cartItems;
    }
}
