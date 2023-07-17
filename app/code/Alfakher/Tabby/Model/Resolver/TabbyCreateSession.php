<?php

declare(strict_types=1);

namespace Alfakher\Tabby\Model\Resolver;

use Alfakher\Tabby\Model\TabbyCheckout;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Store\Model\ScopeInterface;
use Tabby\Checkout\Exception\NotFoundException;

/**
 * Resolver class to make tabby pre-scoring api call
 */
class TabbyCreateSession implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;
    /**
     * @var TabbyCheckout
     */
    private $tabbyCheckout;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param GetCartForUser $getCartForUser
     * @param TabbyCheckout $tabbyCheckout
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        GetCartForUser       $getCartForUser,
        TabbyCheckout        $tabbyCheckout,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->tabbyCheckout = $tabbyCheckout;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Make tabby create session api
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ): array {

        if (empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $maskedCartId = $args['cart_id'];
        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);

        if (!$this->isTabbyEnabled($storeId)) {
            throw new GraphQlInputException(__('Tabby Payment method is not active'));
        }

        if ($cart->getItemsCount() && ($cart->getShippingAddress() || $cart->getBillingAddress())) {
            return $this->tabbyCheckout->tabbyCreateSession($cart);
        }

        return [
            'is_available' => false,
            'rejection_message' => __('Tabby create checkout session failed.')
        ];
    }

    /**
     * Check if tabby installments enabled for particular store
     *
     * @param  int|string $storeId
     * @return bool
     */
    private function isTabbyEnabled($storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'payment/tabby_installments/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
