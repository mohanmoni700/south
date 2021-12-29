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
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\QuoteGraphQl\Model\Cart\GetCartProducts;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\QuoteGraphQl\Model\Resolver\CartItems as SourceCartItems;

class CartItems extends SourceCartItems
{
    /**
     * @var GetCartProducts
     */
    private GetCartProducts $getCartProducts;

    /** @var Uid */
    private Uid $uidEncoder;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param GetCartProducts $getCartProducts
     * @param Uid $uidEncoder
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        GetCartProducts $getCartProducts,
        Uid $uidEncoder,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($getCartProducts, $uidEncoder);

        $this->getCartProducts = $getCartProducts;
        $this->uidEncoder = $uidEncoder;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];

        $itemsData = [];
        if ($cart->getData('has_error')) {
            $errors = $cart->getErrors();
            foreach ($errors as $error) {
                $itemsData[] = new GraphQlInputException(__($error->getText()));
            }
        }

        $cartProductsData = $this->getCartProductsData($cart);
        $cartItems = $cart->getAllVisibleItems();
        $cartItems = $this->filterOutAlfaBundleProducts($cartItems);

        /** @var QuoteItem $cartItem */
        foreach ($cartItems as $cartItem) {
            $productId = $cartItem->getProduct()->getId();
            $alfaBundle = $cartItem->getAlfaBundle();
            $shishaSku = '';
            $charcoalSku = '';

            if ($alfaBundle) {
                $alfaBundle = json_decode($alfaBundle, true);
                $shishaSku = $alfaBundle['shisha_sku'];
                $charcoalSku = $alfaBundle['charcoal_sku'];
            }

            if (!isset($cartProductsData[$productId])) {
                $itemsData[] = new GraphQlNoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
                continue;
            }

            $productData = $cartProductsData[$productId];
            $flavour = $shishaSku ? $this->getBundleProductAttribute($shishaSku): '';
            $charcoalDescription = $charcoalSku
                ? $this->getBundleProductAttribute($charcoalSku, true): '';

            $itemsData[] = [
                'id' => $cartItem->getItemId(),
                'uid' => $this->uidEncoder->encode((string) $cartItem->getItemId()),
                'quantity' => $cartItem->getQty(),
                'product' => $productData,
                'model' => $cartItem,
                'alfa_bundle_flavour' => $flavour,
                'alfa_bundle_charcoal' => $charcoalDescription
            ];
        }

        return $itemsData;
    }

    /**
     * Filters out bundle simple products from cartItems
     *
     * @param  array $cartItems
     * @return array
     */
    private function filterOutAlfaBundleProducts(array $cartItems): array
    {
        return array_filter($cartItems, function ($item) {
            if ($item->getInAlfaBundle() == '1') {
                return false;
            }

            return $item;
        });
    }

    /**
     * Returns shisha flavour or charcoal description for bundle product
     *
     * @param string $sku
     * @param bool $isCharcoal
     * @return string
     * @throws NoSuchEntityException
     */
    private function getBundleProductAttribute(string $sku, bool $isCharcoal = false): string
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            $product = false;
        }

        if (!$product) {
            return '';
        }

        if ($isCharcoal) {
            return $product->getCharcoalShortDetail()
                ? $product->getName() . ': ' . $product->getCharcoalShortDetail() : '';
        }

        return $product->getAttributeText('flavour') ?? '';
    }

    /**
     * Get product data for cart items
     *
     * @param Quote $cart
     * @return array
     */
    private function getCartProductsData(Quote $cart): array
    {
        $products = $this->getCartProducts->execute($cart);
        $productsData = [];
        foreach ($products as $product) {
            $productsData[$product->getId()] = $product->getData();
            $productsData[$product->getId()]['model'] = $product;
            $productsData[$product->getId()]['uid'] = $this->uidEncoder->encode((string) $product->getId());
        }

        return $productsData;
    }
}