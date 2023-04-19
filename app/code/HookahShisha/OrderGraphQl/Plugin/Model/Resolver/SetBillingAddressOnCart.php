<?php
declare(strict_types=1);

namespace HookahShisha\OrderGraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\SetBillingAddressOnCart as MagentoSetBillingAddressOnCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\QuoteRepository;

/**
 * This plugin to save user remote ip address in quote table
 */
class SetBillingAddressOnCart
{
    private $getCartForUser;

    protected $quoteRepository;

    /**
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        QuoteRepository $quoteRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     *  Save remote ip address.
     *
     * @param MagentoSetBillingAddressOnCart $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws GraphQlInputException
     */
    public function beforeResolve(
        MagentoSetBillingAddressOnCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($args['input']['cart_id']) && isset($args['input']['remote_ip'])) {
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $maskedCartId = $args['input']['cart_id'];
            $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
            $cartId = $cart->getId();

            $quote = $this->quoteRepository->get($cartId);
            $quote->setData('remote_ip', $args['input']['remote_ip']);
            $this->quoteRepository->save($quote);
        }
    }
}
