<?php
declare (strict_types = 1);

namespace HookahShisha\Checkoutchanges\Plugin\QuoteGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\MergeCarts\CartQuantityValidatorInterface;
use Magento\QuoteGraphQl\Model\Resolver\MergeCarts as Subject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Psr\Log\LoggerInterface;

/**
 * Merge Carts Resolver
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class MergeCartsPlugin
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerCartResolver
     */
    private $customerCartResolver;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var CartQuantityValidatorInterface
     */
    private $cartQuantityValidator;

    /**
     * [__construct]
     *
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerCartResolver|null $customerCartResolver
     * @param QuoteIdToMaskedQuoteIdInterface|null $quoteIdToMaskedQuoteId
     * @param CartQuantityValidatorInterface|null $cartQuantityValidator
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        CustomerCartResolver $customerCartResolver = null,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId = null,
        CartQuantityValidatorInterface $cartQuantityValidator = null,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->customerCartResolver = $customerCartResolver
        ?: ObjectManager::getInstance()->get(CustomerCartResolver::class);
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId
        ?: ObjectManager::getInstance()->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->cartQuantityValidator = $cartQuantityValidator
        ?: ObjectManager::getInstance()->get(CartQuantityValidatorInterface::class);
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * [beforeResolve]
     *
     * @param Subject $subject
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws GraphQlInputException
     */
    public function beforeResolve(
        Subject $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__(
                'Required parameter "source_cart_id" is missing'
            ));
        }

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__(
                'The current customer isn\'t authorized.'
            ));
        }
        $currentUserId = $context->getUserId();

        if (!isset($args['destination_cart_id'])) {
            try {
                $cart = $this->customerCartResolver->resolve($currentUserId);
            } catch (CouldNotSaveException $exception) {
                throw new GraphQlNoSuchEntityException(
                    __('Could not create empty cart for customer'),
                    $exception
                );
            }
            $customerMaskedCartId = $this->quoteIdToMaskedQuoteId->execute(
                (int) $cart->getId()
            );
        } else {
            if (empty($args['destination_cart_id'])) {
                throw new GraphQlInputException(__(
                    'The parameter "destination_cart_id" cannot be empty'
                ));
            }
        }
        $guestMaskedCartId = $args['source_cart_id'];
        $customerMaskedCartId = $customerMaskedCartId ?? $args['destination_cart_id'];
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        try {
            $guestCart = $this->getCartForUser->execute(
                $guestMaskedCartId,
                null,
                $storeId
            );
            $customerCart = $this->getCartForUser->execute(
                $customerMaskedCartId,
                $currentUserId,
                $storeId
            );
            if ($this->cartQuantityValidator->validateFinalCartQuantities($customerCart, $guestCart)) {
                $guestCart = $this->getCartForUser->execute(
                    $guestMaskedCartId,
                    null,
                    $storeId
                );
            }

            if ($guestCart && $guestCart->getShippingAddress()) {
                    $customer=$this->customerRepository->getById($currentUserId);
                    $shippingAddressId = $customer->getDefaultShipping();
                if ($shippingAddressId === null || $shippingAddressId === 0) {
                    $customerCart->getShippingAddress()
                        ->setFirstname($guestCart->getShippingAddress()->getFirstname());
                    $customerCart->getShippingAddress()
                        ->setLastname($guestCart->getShippingAddress()->getLastname());
                    $customerCart->getShippingAddress()
                        ->setStreet($guestCart->getShippingAddress()->getStreet());
                    $customerCart->getShippingAddress()
                        ->setCity($guestCart->getShippingAddress()->getCity());
                    $customerCart->getShippingAddress()
                        ->setRegion($guestCart->getShippingAddress()->getRegion());
                    $customerCart->getShippingAddress()
                        ->setRegionId($guestCart->getShippingAddress()->getRegionId());
                    $customerCart->getShippingAddress()
                        ->setPostcode($guestCart->getShippingAddress()->getPostcode());
                    $customerCart->getShippingAddress()
                        ->setCountryId($guestCart->getShippingAddress()->getCountryId());
                    $customerCart->getShippingAddress()
                        ->setTelephone($guestCart->getShippingAddress()->getTelephone());
                    $customerCart->getShippingAddress()
                        ->setCounty($guestCart->getShippingAddress()->getCounty());
                }
            }
            try {
                $this->cartRepository->save($customerCart);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                throw new GraphQlInputException(__($e->getMessage()));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
