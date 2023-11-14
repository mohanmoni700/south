<?php

declare(strict_types=1);

namespace Alfakher\StoreCredit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\CustomerBalance\Api\BalanceManagementInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;

class ApplyStoreCreditToCart implements ResolverInterface
{
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
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->customerBalanceHelper->isEnabled()) {
            throw new GraphQlInputException(__('You cannot add "%1" to the cart.', 'credit'));
        }

        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', 'cart_id'));
        }
        $maskedCartId = $args['input']['cart_id'];

        $currentUserId = $context->getUserId();

        if (empty($currentUserId)) {
            throw new GraphQlAuthorizationException(__('Please specify a valid customer'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $cartId = $cart->getId();

        $this->setFullStoreCreditFlag($cart);

        $this->balanceManagement->apply($cartId);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Set store credit type
     *
     * @param Quote $cart
     * @return void
     */
    private function setFullStoreCreditFlag($cart)
    {
        $cart->setStorecreditPartialAmount(0);
        $cart->setStorecreditType('all');
        $this->cartRepository->save($cart);
    }
}
