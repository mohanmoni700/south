<?php
declare (strict_types = 1);

namespace HookahShisha\Checkoutchanges\Plugin\QuoteGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\MergeCarts as Subject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Psr\Log\LoggerInterface;

/**
 * Merge Carts Resolver
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class MergeCartsPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * [__construct]
     *
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * [afterResolve]
     *
     * @param Subject $subject
     * @param mixed $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     *
     * @return $result
     */
    public function afterResolve(
        Subject $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $currentUserId = $context->getUserId();
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__(
                'Required parameter "source_cart_id" is missing'
            ));
        }
        $guestMaskedCartId = $args['source_cart_id'];
        $cartId = $this->maskedQuoteIdToQuoteId->execute($guestMaskedCartId);

        try {
            /** @var Quote $cart */
            $guestCart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $cartId])
            );
        }

        $customerCart = $result['model'];

        if ($customerCart != null && $guestCart && $guestCart->getShippingAddress()) {
            $customer = $this->customerRepository->getById($currentUserId);
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
        }
        return $result;
    }
}
