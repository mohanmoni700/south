<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model\Cart;

use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Amasty\Promo\Helper\Cart;
use Amasty\Promo\Model\PromoItemRepository;
use Amasty\Promo\Model\Rule;

/**
 * Add simple product to cart
 */
class AddFreeProduct
{
    const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    protected $promoCartHelper;

    protected PromoItemRepository $promoItemRepository;


    /**
     * Request whitelist parameters
     * @var array
     */
    private $requestOptions = [
        'super_attribute',
        'options',
        'super_attribute',
        'links',
        'giftcard_sender_name',
        'giftcard_sender_email',
        'giftcard_recipient_name',
        'giftcard_recipient_email',
        'giftcard_message',
        'giftcard_amount',
        'custom_giftcard_amount'
    ];

    /**
     * AddFreeProduct constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $promoCartHelper
     * @param PromoItemRepository $promoItemRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Cart $promoCartHelper,
        PromoItemRepository $promoItemRepository
    ) {
        $this->productRepository = $productRepository;
        $this->promoCartHelper = $promoCartHelper;
        $this->promoItemRepository = $promoItemRepository;
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $sku = $this->extractSku($cartItemData);

        try {
            $product = $this->productRepository->get($sku, false, null, false);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
            $params = [
                'isPromoItems' => 'true',
                'product_id' => $product->getId(),
                'rule_id' => isset($cartItemData['data']['rule_id']) ? $cartItemData['data']['rule_id'] : null ,
                'allowed_quantity' => isset($cartItemData['data']['max_qty']) ? $cartItemData['data']['max_qty'] : null,
                'quantity' => isset($cartItemData['data']['quantity']) ? $cartItemData['data']['quantity'] : null
            ];

            $promoDataItem = $this->getPromoDataItem($sku, $params, $cart);
            if ($promoDataItem && isset($product)) {
                $qty = 1;
                $requestOptions = array_intersect_key($params, array_flip($this->requestOptions));
                $result = $this->promoCartHelper->addProduct(
                    $product,
                    $qty,
                    $promoDataItem,
                    $requestOptions,
                    $cart
                );
                if (!$result) {
                    throw new GraphQlInputException(
                        __(
                            'Product that you are trying to add is not available.'
                        )
                    );
                }
            } else {
                throw new GraphQlInputException(
                    __(
                        'No cart rule related to add free product to cart'
                    )
                );
            }
        } catch (Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            $e = new GraphQlInputException(__('Cannot add product to cart'));
            $errors = array_unique(explode("\n", $result));
            foreach ($errors as $error) {
                $e->addError(new GraphQlInputException(__($error)));
            }
            throw $e;
        }
    }

    /**
     * Extract SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractSku(array $cartItemData): string
    {
        if (!empty($cartItemData['parent_sku'])) {
            return (string)$cartItemData['parent_sku'];
        }
        if (empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }
        return (string)$cartItemData['data']['sku'];
    }

    /**
     * @param $sku
     * @param $params
     * @return PromoItemData|null
     */
    protected function getPromoDataItem($sku, $params, $quote)
    {
        $promoItemData = null;
        $promoItemsGroup = $this->promoItemRepository->getItemsByQuoteId((int)$quote->getId());
        if (!empty($params['rule_id'])) {
            $promoItemData = $promoItemsGroup->registerItem(
                $sku,
                $params['quantity'],
                $params['rule_id'],
                Rule::RULE_TYPE_ALL,
                null,
                null,
                $params['allowed_quantity']
            );
        }
        return $promoItemData;
    }
}
