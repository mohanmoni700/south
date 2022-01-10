<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\Cart\CreateBuyRequest;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartItem as SourceUpdateCartItem;

class UpdateCartItem extends SourceUpdateCartItem
{
    /**
     * @var CreateBuyRequest
     */
    private CreateBuyRequest $createBuyRequest;

    /**
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param CreateBuyRequest $createBuyRequest
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $quoteRepository,
        CreateBuyRequest $createBuyRequest
    ) {
        parent::__construct($cartItemRepository, $quoteRepository, $createBuyRequest);

        $this->createBuyRequest = $createBuyRequest;
    }

    /**
     * Update cart item
     *
     * @param Quote $cart
     * @param int $cartItemId
     * @param float $quantity
     * @param array $customizableOptionsData
     * @return void
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function execute(Quote $cart, int $cartItemId, float $quantity, array $customizableOptionsData): void
    {
        if (empty($customizableOptionsData)) { // Update only item's qty
            $this->updateItemQuantity($cartItemId, $cart, $quantity);

            return;
        }

        try {
            $result = $cart->updateItem(
                $cartItemId,
                $this->createBuyRequest->execute($quantity, $customizableOptionsData)
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(
                __(
                    'Could not update cart item: %message',
                    ['message' => $e->getMessage()]
                )
            );
        }

        if ($result->getHasError()) {
            throw new GraphQlInputException(
                __(
                    'Could not update cart item: %message',
                    ['message' => $result->getMessage()]
                )
            );
        }
    }

    /**
     * Updates item qty for the specified cart
     *
     * @param int $itemId
     * @param Quote $cart
     * @param float $quantity
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     */
    private function updateItemQuantity(int $itemId, Quote $cart, float $quantity)
    {
        $cartItem = $cart->getItemById($itemId);
        if ($cartItem === false) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find cart item with id: %1.', $itemId)
            );
        }
        $cartItem->setQty($quantity);
        $this->validateCartItem($cartItem);
    }

    /**
     * Validate cart item
     *
     * @param Item $cartItem
     * @return void
     * @throws GraphQlInputException
     */
    private function validateCartItem(Item $cartItem): void
    {
        if ($cartItem->getHasError()) {
            $errors = [];
            foreach ($cartItem->getMessage(false) as $message) {
                $isInAlfaBundle = $cartItem->getParentAlfaBundle();
                $useCustomMessage = $isInAlfaBundle && $message == 'The requested qty is not available';
                // We use custom message for products in alfa bundle if requested qty is not available
                // Also we use the same message for shisha and charcoal because first error is immediately thrown
                $message = $useCustomMessage
                    ? __('The requested qty for included shisha/charcoal is not available')
                    : $message;

                $errors[] = $message;
            }
            if (!empty($errors)) {
                throw new GraphQlInputException(
                    __(
                        'Could not update the product with SKU %sku: %message',
                        ['sku' => $cartItem->getSku(), 'message' => __(implode("\n", $errors))]
                    )
                );
            }
        }
    }
}
