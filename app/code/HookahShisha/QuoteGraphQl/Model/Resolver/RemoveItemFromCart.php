<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\RemoveItemFromCart as SourceRemoveItemFromCart;

class RemoveItemFromCart extends SourceRemoveItemFromCart
{
    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var CartItemRepositoryInterface
     */
    private CartItemRepositoryInterface $cartItemRepository;

    /**
     * @var MaskedQuoteIdToQuoteId
     */
    private MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId;

    /**
     * @var ArgumentsProcessorInterface
     */
    private ArgumentsProcessorInterface $argsSelection;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param ArgumentsProcessorInterface $argsSelection
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        ArgumentsProcessorInterface $argsSelection
    ) {
        parent::__construct($getCartForUser, $cartItemRepository, $maskedQuoteIdToQuoteId, $argsSelection);

        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
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
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $maskedCartId])
            );
        }

        if (empty($processedArgs['input']['cart_item_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_item_id" is missing.'));
        }
        $itemId = $processedArgs['input']['cart_item_id'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $this->deleteAssociatedBundleItems($cartId, $itemId);
            $this->cartItemRepository->deleteById($cartId, $itemId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('The cart doesn\'t contain the item'));
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
     * Deletes associated bundle products (shisha, charcoal)
     *
     * @param string $cartId
     * @param int $itemId
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function deleteAssociatedBundleItems(string $cartId, int $itemId)
    {
        $cartItems = $this->cartItemRepository->getList($cartId);
        $cartItem = $this->getCartItem($cartItems, $itemId);
        $alfaBundle = $cartItem->getAlfaBundle();

        if (!$alfaBundle) {
            return;
        }

        $alfaBundle = json_decode($alfaBundle, true);
        foreach ($alfaBundle as $item => $sku) {
            $id = $sku ? $this->getCartItemIdBySku($cartItems, $sku) : '';

            if ($id) {
                $this->cartItemRepository->deleteById($cartId, $id);
            }
        }
    }

    /**
     * Returns cart item
     *
     * @param array $cartItems
     * @param int $cartItemId
     * @return CartItemInterface
     * @throws NoSuchEntityException
     */
    private function getCartItem(array $cartItems, int $cartItemId): CartItemInterface
    {
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getItemId() == $cartItemId) {
                return $cartItem;
            }
        }

        throw NoSuchEntityException::singleField('cartItemId', $cartItemId);
    }

    /**
     * Returns cart item id by cart item sku
     *
     * @param array $cartItems
     * @param string $cartItemSku
     * @return string|null
     * @throws NoSuchEntityException
     */
    private function getCartItemIdBySku(array $cartItems, string $cartItemSku): ?string
    {
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getSku() == $cartItemSku) {
                return $cartItem->getItemId();
            }
        }

        return null;
    }
}
