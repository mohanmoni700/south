<?php

declare(strict_types=1);

namespace Alfakher\StoreCredit\Model\Resolver;

use Magento\CustomerBalance\Api\BalanceManagementInterface;
use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

class ApplyPartialStoreCreditToCart implements ResolverInterface
{
    private const STORE_CREDIT_PARTIAL = 'partial';

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var BalanceManagementInterface
     */
    private $balanceManagement;

    /**
     * @var CustomerBalanceHelper
     */
    private $customerBalanceHelper;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param BalanceManagementInterface $balanceManagement
     * @param CustomerBalanceHelper $customerBalanceHelper
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        GetCartForUser             $getCartForUser,
        BalanceManagementInterface $balanceManagement,
        CustomerBalanceHelper      $customerBalanceHelper,
        CartRepositoryInterface    $cartRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->balanceManagement = $balanceManagement;
        $this->customerBalanceHelper = $customerBalanceHelper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Graphql query resolver
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws NoSuchEntityException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        $args = $this->validateArgs($args);

        $maskedCartId = $args['input']['cart_id'];
        $currentUserId = $context->getUserId();

        if (empty($currentUserId)) {
            throw new GraphQlAuthorizationException(__('Please specify a valid customer'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $cart->setStorecreditType(self::STORE_CREDIT_PARTIAL);
        $cart->setStorecreditPartialAmount($args['input']['amount']);
        $this->cartRepository->save($cart);
        $cartId = $cart->getId();

        $this->balanceManagement->apply($cartId);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Validate query arguments
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): array
    {
        if (!$this->customerBalanceHelper->isEnabled()) {
            throw new GraphQlInputException(__('You cannot add "%1" to the cart.', 'credit'));
        }

        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', 'cart_id'));
        }

        if (isset($args['input']['amount']) && $args['input']['amount'] <= 0) {
            throw new GraphQlInputException(__('Required parameter "%1" should be greater than zero', 'amount'));
        }

        return $args;
    }
}
