<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Quote\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as SourceAddProductsToCart;
use Magento\Quote\Model\Cart\BuyRequest\BuyRequestBuilder;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\Quote\Model\Cart\Data\CartItem;

class AddProductsToCart extends SourceAddProductsToCart
{
    /**
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';
    private const ERROR_NOT_SALABLE = 'NOT_SALABLE';
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * List of error messages and codes.
     */
    private const MESSAGE_CODES = [
        'Could not find a product with SKU' => self::ERROR_PRODUCT_NOT_FOUND,
        'The required options you selected are not available' => self::ERROR_NOT_SALABLE,
        'Product that you are trying to add is not available.' => self::ERROR_NOT_SALABLE,
        'This product is out of stock' => self::ERROR_INSUFFICIENT_STOCK,
        'There are no source items' => self::ERROR_NOT_SALABLE,
        'The fewest you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The most you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The requested qty is not available' => self::ERROR_INSUFFICIENT_STOCK,
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param BuyRequestBuilder $requestBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        BuyRequestBuilder $requestBuilder
    ) {
        parent::__construct(
            $productRepository,
            $cartRepository,
            $maskedQuoteIdToQuoteId,
            $requestBuilder
        );

        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * Add cart items to the cart
     *
     * @param string $maskedCartId
     * @param CartItem[] $cartItems
     * @return AddProductsToCartOutput
     * @throws NoSuchEntityException Could not find a Cart with provided $maskedCartId
     */
    public function execute(string $maskedCartId, array $cartItems): AddProductsToCartOutput
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        $cart = $this->cartRepository->get($cartId);

        foreach ($cartItems as $cartItemPosition => $cartItem) {
            $this->addItemToCart($cart, $cartItem, $cartItemPosition, $cartItems);
        }

        if ($cart->getData('has_error')) {
            $cartErrors = $cart->getErrors();

            /** @var MessageInterface $error */
            foreach ($cartErrors as $error) {
                $this->addError($error->getText());
            }
        }

        if (count($this->errors) !== 0) {
            /* Revert changes introduced by add to cart processes in case of an error */
            $cart->getItemsCollection()->clear();
        } else {
            // Save cart only when all items are added to cart and no errors occurred
            $this->cartRepository->save($cart);
        }

        return $this->prepareErrorOutput($cart);
    }

    /**
     * Get SuperPack array
     *
     * @param CartItem $cartItem
     * @return bool | array
     */
    private function getSuperPackCartItem($cartItem)
    {
        $alfaBundle = $cartItem->getAlfaBundle();
        if ($alfaBundle) {
            $alfaBundle = json_decode($alfaBundle, true);
            if (isset($alfaBundle['super_pack']) && $alfaBundle['super_pack'] && is_array($alfaBundle)) {
                return $alfaBundle['super_pack'];
            }
        }
        return false;
    }

    /**
     * Get SuperPack array
     *
     * @param CartInterface $cart
     * @param array $item
     * @return Item|string
     */
    private function addSuperPackProductToCart(
        CartInterface $cart,
        array $item
    ) {
        $cartItem = (new Data\CartItemFactory())->create($item);
        try {
            $product = $this->productRepository->get($cartItem->getSku(), false, null, true);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not find a product with SKU "%sku"', ['sku' => $cartItem->getSku()])
            );
        }
        return $cart->addProduct($product, $this->requestBuilder->build($cartItem));
    }

    /**
     * Adds a particular item to the shopping cart
     *
     * @param CartInterface|Quote $cart
     * @param Data\CartItem $cartItem
     * @param int $cartItemPosition
     * @param array $cartItems
     */
    private function addItemToCart(
        CartInterface $cart,
        CartItem $cartItem,
        int $cartItemPosition,
        array $cartItems
    ): void {
        $sku = $cartItem->getSku();

        if ($cartItem->getQuantity() <= 0) {
            $this->addError(__('The product quantity should be greater than 0')->render());

            return;
        }

        $superPack = $this->getSuperPackCartItem($cartItem);
        try {
            $product = $this->productRepository->get($sku, false, null, true);
            if ($superPack) {
                foreach ($superPack as $item) {
                    $res = $this->addSuperPackProductToCart($cart, $item);
                    $this->addedSupePack[] = $res;
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->addError(
                __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                $cartItemPosition
            );

            return;
        }

        try {
            $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
        } catch (\Throwable $e) {
            $isInAlfaBundle = $cartItem->getParentAlfaBundle();
            $alfaBundleProductType = $isInAlfaBundle
                ? $this->getAlfaBundleProductType($cartItem->getSku(), $cartItems)
                : '';
            // We use custom message for products in alfa bundle if requested qty is not available
            $useCustomMessage = $e->getMessage() == 'The requested qty is not available';
            $customMessage = __('The requested %1 qty is not available', $alfaBundleProductType);

            $this->addError(
                __($useCustomMessage ? $customMessage : $e->getMessage())->render(),
                $cartItemPosition
            );
            $cart->setHasError(false);

            return;
        }

        if (is_string($result)) {
            $resultErrors = array_unique(explode("\n", $result));
            foreach ($resultErrors as $error) {
                $this->addError(__($error)->render(), $cartItemPosition);
            }
        }
    }

    /**
     * Add order line item error
     *
     * @param string $message
     * @param int $cartItemPosition
     * @return void
     */
    private function addError(string $message, int $cartItemPosition = 0): void
    {
        $this->errors[] = new Error(
            $message,
            $this->getErrorCode($message),
            $cartItemPosition
        );
    }

    /**
     * Get message error code.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        foreach (self::MESSAGE_CODES as $codeMessage => $code) {
            if (false !== stripos($message, $codeMessage)) {
                return $code;
            }
        }

        /* If no code was matched, return the default one */
        return self::ERROR_UNDEFINED;
    }

    /**
     * Creates a new output from existing errors
     *
     * @param CartInterface $cart
     * @return AddProductsToCartOutput
     */
    private function prepareErrorOutput(CartInterface $cart): AddProductsToCartOutput
    {
        $output = new AddProductsToCartOutput($cart, $this->errors);
        $this->errors = [];
        $cart->setHasError(false);

        return $output;
    }

    /**
     * Returns alfa bundle product type (shisha || charcoal)
     *
     * @param string $sku
     * @param array $items
     * @return string
     */
    private function getAlfaBundleProductType(string $sku, array $items): string
    {
        $type = [
            'shisha_sku' => 'shisha',
            'charcoal_sku' => 'charcoal'
        ];
        $alfaBundle = [];

        foreach ($items as $item) {
            $alfaBundle = $item->getAlfaBundle();

            if ($alfaBundle) {
                $alfaBundle = json_decode($alfaBundle, true);

                break;
            }
        }

        return $type[array_search($sku, $alfaBundle)];
    }
}
