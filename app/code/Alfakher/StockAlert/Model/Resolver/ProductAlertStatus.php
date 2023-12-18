<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\Resolver;

use Alfakher\StockAlert\Model\ProductAlertStockGuestUserFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;

class ProductAlertStatus implements ResolverInterface
{
    /**
     * @var ProductAlertStockGuestUserRepositoryInterface
     */
    protected ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository;

    /**
     * @var ProductAlertStockGuestUserFactory
     */
    protected ProductAlertStockGuestUserFactory $guestSubscriptionFactory;

    /**
     * @param ProductAlertStockGuestUserFactory $guestSubscriptionFactory
     * @param ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository
     */
    public function __construct(
        ProductAlertStockGuestUserFactory $guestSubscriptionFactory,
        ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository
    ) {
        $this->guestSubscriptionFactory = $guestSubscriptionFactory;
        $this->guestSubscriptionRepository = $guestSubscriptionRepository;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string[]
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $email = $args['input']['email'];
        $name = $args['input']['name'];
        $productId = $args['input']['product_id'];

        // Load the product alert entry using email, name, and product id
        $guestSubscriptionModel = $this->guestSubscriptionRepository->get($email, $name, $productId);
        // Check if any record exists with the given criteria
        $status = $guestSubscriptionModel ? 'You are already Subscribed' : 'Not Subscribed';

        return ['status' => $status];
    }
}
